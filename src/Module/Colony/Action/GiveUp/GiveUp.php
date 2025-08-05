<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\GiveUp;

use Override;
use request;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class GiveUp implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    public function __construct(
        private GiveUpRequestInterface $giveupRequest,
        private ColonyRepositoryInterface $colonyRepository,
        private ColonyResetterInterface $colonyResetter,
        private UserRepositoryInterface $userRepository,
        private ComponentRegistrationInterface $componentRegistration
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $planetAmount = $this->colonyRepository->getAmountByUser($user, ColonyTypeEnum::PLANET);
        $colony = $this->colonyRepository->find($this->giveupRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $userId) {
            throw new AccessViolationException();
        }

        $code = request::postString('giveupcode');
        if ($code === false) {
            return;
        }
        $trimmedCode = trim($code);

        if ($trimmedCode !== substr(md5($colony->getName()), 0, 6)) {
            $game->getInfo()->addInformation(_('Der Bestätigungscode war fehlerhaft'));
            return;
        }

        $this->colonyResetter->reset($colony);

        $isPlanet = $colony->getColonyClass()->isPlanet();
        if ($isPlanet && $planetAmount === 1) {
            $user->setState(UserStateEnum::UNCOLONIZED);

            $this->userRepository->save($user);
        }

        $this->componentRegistration->addComponentUpdate(GameComponentEnum::COLONIES);

        $game->getInfo()->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
