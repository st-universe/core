<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Override;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PromotePlayer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROMOTE_USER';

    public function __construct(
        private PromotePlayerRequestInterface $promotePlayerRequest,
        private AllianceJobRepositoryInterface $allianceJobRepository,
        private AllianceActionManagerInterface $allianceActionManager,
        private PrivateMessageSenderInterface $privateMessageSender,
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolationException();
        }

        $playerId = $this->promotePlayerRequest->getPlayerId();

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAlliance() !== $alliance) {
            throw new AccessViolationException();
        }

        $type = AllianceJobTypeEnum::from($this->promotePlayerRequest->getPromotionType());
        $availablePromotions = [
            AllianceJobTypeEnum::FOUNDER,
            AllianceJobTypeEnum::SUCCESSOR,
            AllianceJobTypeEnum::DIPLOMATIC,
        ];

        if (!in_array($type, $availablePromotions)) {
            throw new AccessViolationException();
        }

        $founderJob = $alliance->getFounder();

        if ($founderJob->getUserId() === $playerId) {
            throw new AccessViolationException();
        }

        $this->allianceJobRepository->truncateByUser($playerId);
        $alliance->getJobs()->remove($type->value);

        $text = '';
        $view = Management::VIEW_IDENTIFIER;

        switch ($type) {
            case AllianceJobTypeEnum::FOUNDER:
                if ($founderJob->getUserId() !== $userId) {
                    throw new AccessViolationException();
                }
                $this->allianceActionManager->setJobForUser(
                    $alliance,
                    $player,
                    AllianceJobTypeEnum::FOUNDER
                );
                $text = sprintf(
                    _('Du wurdest zum neuen Präsidenten der Allianz %s ernannt'),
                    $alliance->getName()
                );
                $view = ModuleEnum::ALLIANCE;
                break;
            case AllianceJobTypeEnum::SUCCESSOR:
                if ($userId === $playerId) {
                    throw new AccessViolationException();
                }

                $this->allianceActionManager->setJobForUser(
                    $alliance,
                    $player,
                    AllianceJobTypeEnum::SUCCESSOR
                );

                $text = sprintf(
                    _('Du wurdest zum neuen Vize-Präsidenten der Allianz %s ernannt'),
                    $alliance->getName()
                );
                break;
            case AllianceJobTypeEnum::DIPLOMATIC:
                if ($userId === $playerId) {
                    throw new AccessViolationException();
                }

                $this->allianceActionManager->setJobForUser(
                    $alliance,
                    $player,
                    AllianceJobTypeEnum::DIPLOMATIC
                );

                $text = sprintf(
                    'Du wurdest zum neuen Außenminister der Allianz %s ernannt',
                    $alliance->getName()
                );
                break;
        }

        $this->privateMessageSender->send($userId, $playerId, $text);

        $game->setView($view);

        $game->addInformation(_('Das Mitglied wurde befördert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
