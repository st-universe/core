<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ActivateVacation;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\TimeConstants;
use Stu\Lib\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ActivateVacation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE_VACATION';

    public function __construct(private SessionInterface $session, private UserRepositoryInterface $userRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //wenn die letzte Aktivierung älter als eine Woche ist
        if ((time() - $game->getUser()->getVacationRequestDate()) > TimeConstants::SEVEN_DAYS_IN_SECONDS) {
            $user = $game->getUser();

            $user->setVacationMode(true);
            $user->setVacationRequestDate(time());

            $this->userRepository->save($user);

            $this->session->logout();

            $game->redirectTo(sprintf('/%s.php', ModuleEnum::INDEX->value));
        } else {
            $game->addInformation(
                _('Urlaubsmodus ist noch gesperrt. Letzte Aktivierung ist weniger als eine Woche her!')
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
