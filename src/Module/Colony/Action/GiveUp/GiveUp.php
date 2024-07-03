<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\GiveUp;

use Override;
use request;
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Game\Lib\Component\ComponentEnum;
use Stu\Module\Game\Lib\Component\ComponentLoaderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class GiveUp implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    public function __construct(private GiveUpRequestInterface $giveupRequest, private ColonyRepositoryInterface $colonyRepository, private ColonyResetterInterface $colonyResetter, private UserRepositoryInterface $userRepository, private ComponentLoaderInterface $componentLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $planetAmount = $this->colonyRepository->getAmountByUser($user, ColonyTypeEnum::COLONY_TYPE_PLANET);
        $colony = $this->colonyRepository->find($this->giveupRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $code = request::postString('giveupcode');
        if ($code === false) {
            return;
        }
        $trimmedCode = trim($code);

        if ($trimmedCode !== substr(md5($colony->getName()), 0, 6)) {
            $game->addInformation(_('Der Bestätigungscode war fehlerhaft'));
            return;
        }

        $this->colonyResetter->reset($colony);

        $isPlanet = $colony->getColonyClass()->isPlanet();
        if ($isPlanet && $planetAmount === 1) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);

            $this->userRepository->save($user);
        }

        $this->componentLoader->addComponentUpdate(ComponentEnum::COLONIES_NAVLET);

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
