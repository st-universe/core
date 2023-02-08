<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PromotePlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PROMOTE_USER';

    private PromotePlayerRequestInterface $promotePlayerRequest;

    private AllianceJobRepositoryInterface $allianceJobRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private PrivateMessageSenderInterface $privateMessageSender;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        PromotePlayerRequestInterface $promotePlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository,
        AllianceActionManagerInterface $allianceActionManager,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->promotePlayerRequest = $promotePlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        if (!$this->allianceActionManager->mayEdit($allianceId, $userId)) {
            throw new AccessViolation();
        }
        $playerId = $this->promotePlayerRequest->getPlayerId();

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $type = $this->promotePlayerRequest->getPromotionType();
        $availablePromotions = [
            AllianceEnum::ALLIANCE_JOBS_FOUNDER,
            AllianceEnum::ALLIANCE_JOBS_SUCCESSOR,
            AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC,
        ];

        if (!in_array($type, $availablePromotions)) {
            throw new AccessViolation();
        }
        if ($alliance->getFounder()->getUserId() == $playerId) {
            throw new AccessViolation();
        }

        $this->allianceJobRepository->truncateByUser($playerId);

        $text = '';

        switch ($type) {
            case AllianceEnum::ALLIANCE_JOBS_FOUNDER:
                $founderJob = $this->allianceJobRepository->getSingleResultByAllianceAndType(
                    $allianceId,
                    AllianceEnum::ALLIANCE_JOBS_FOUNDER
                );
                if ($founderJob->getUserId() !== $userId) {
                    throw new AccessViolation();
                }
                $this->allianceActionManager->setJobForUser(
                    $allianceId,
                    $userId,
                    AllianceEnum::ALLIANCE_JOBS_SUCCESSOR
                );
                $this->allianceActionManager->setJobForUser(
                    $allianceId,
                    $playerId,
                    AllianceEnum::ALLIANCE_JOBS_FOUNDER
                );
                $text = sprintf(
                    _('Du wurdest zum neuen Präsidenten der Allianz %s ernannt'),
                    $alliance->getName()
                );
                break;
            case AllianceEnum::ALLIANCE_JOBS_SUCCESSOR:
                if ($userId === $playerId) {
                    throw new AccessViolation();
                }
                $this->allianceActionManager->setJobForUser(
                    $allianceId,
                    $playerId,
                    AllianceEnum::ALLIANCE_JOBS_SUCCESSOR
                );

                $text = sprintf(
                    _('Du wurdest zum neuen Vize-Präsidenten der Allianz %s ernannt'),
                    $alliance->getName()
                );
                break;
            case AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC:
                if ($userId === $playerId) {
                    throw new AccessViolation();
                }
                $this->allianceActionManager->setJobForUser(
                    $allianceId,
                    $playerId,
                    AllianceEnum::ALLIANCE_JOBS_DIPLOMATIC
                );

                $text = sprintf(
                    'Du wurdest zum neuen Außenminister der Allianz %s ernannt',
                    $alliance->getName()
                );
                break;
        }

        $this->privateMessageSender->send($userId, $playerId, $text);

        $game->setView(Management::VIEW_IDENTIFIER);

        $game->addInformation(_('Das Mitglied wurde befördert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
