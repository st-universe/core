<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\Leave;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Leave implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_LEAVE_ALLIANCE';

    public function __construct(private AllianceJobRepositoryInterface $allianceJobRepository, private PrivateMessageSenderInterface $privateMessageSender, private UserRepositoryInterface $userRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $userId = $user->getId();

        $foundJob = $this->allianceJobRepository->getSingleResultByAllianceAndType(
            $alliance->getId(),
            AllianceEnum::ALLIANCE_JOBS_FOUNDER
        );

        if ($foundJob->getUserId() === $userId) {
            throw new AccessViolation();
        }

        $this->allianceJobRepository->truncateByUser($userId);

        $user->setAlliance(null);

        $this->userRepository->save($user);

        $text = sprintf(
            'Der Siedler %s hat die Allianz verlassen',
            $user->getName()
        );

        $this->privateMessageSender->send($userId, $foundJob->getUserId(), $text);
        if ($alliance->getSuccessor() !== null) {
            $this->privateMessageSender->send($userId, $alliance->getSuccessor()->getUserId(), $text);
        }

        $game->setView(ModuleEnum::ALLIANCE);

        $game->addInformation(_('Du hast die Allianz verlassen'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
