<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Leave;

use Override;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Leave implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LEAVE_ALLIANCE';

    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository,
        private AllianceJobManagerInterface $allianceJobManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        if ($alliance === null) {
            throw new SanityCheckException();
        }
        $userId = $user->getId();

        $this->allianceJobManager->removeUserFromAllJobs($user, $alliance);

        $user->setAlliance(null);
        $this->userRepository->save($user);

        $text = sprintf(
            'Der Siedler %s hat die Allianz verlassen',
            $user->getName()
        );

        $founderJob = $alliance->getFounder();
        foreach ($founderJob->getUsers() as $founder) {
            $this->privateMessageSender->send($userId, $founder->getId(), $text);
        }

        $successorJob = $alliance->getSuccessor();
        if ($successorJob !== null) {
            foreach ($successorJob->getUsers() as $successor) {
                $this->privateMessageSender->send($userId, $successor->getId(), $text);
            }
        }

        $game->setView(ModuleEnum::ALLIANCE);

        $game->getInfo()->addInformation(_('Du hast die Allianz verlassen'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
