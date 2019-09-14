<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use AccessViolation;
use PM;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use User;

final class PromotePlayer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PROMOTE_USER';

    private $promotePlayerRequest;

    private $allianceJobRepository;

    public function __construct(
        PromotePlayerRequestInterface $promotePlayerRequest,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->promotePlayerRequest = $promotePlayerRequest;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();

        if (!$alliance->currentUserMayEdit()) {
            throw new AccessViolation();
        }
        $playerId = $this->promotePlayerRequest->getPlayerId();
        $player = new User($playerId);

        if ($player->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $type = $this->promotePlayerRequest->getPromotionType();
        $availablePromotions = [
            ALLIANCE_JOBS_FOUNDER,
            ALLIANCE_JOBS_SUCCESSOR,
            ALLIANCE_JOBS_DIPLOMATIC,
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
            case ALLIANCE_JOBS_FOUNDER:
                if (!$alliance->currentUserIsFounder()) {
                    throw new AccessViolation();
                }
                $alliance->setFounder($playerId);
                $text = sprintf(
                    _('Du wurdest zum neuen Präsidenten der Allianz %s ernannt'),
                    $alliance->getNameWithoutMarkup()
                );
                break;
            case ALLIANCE_JOBS_SUCCESSOR:
                if ($userId === $playerId) {
                    throw new AccessViolation();
                }
                $alliance->setSuccessor($playerId);
                $text = sprintf(
                    _('Du wurdest zum neuen Vize-Präsidenten der Allianz %s ernannt'),
                    $alliance->getNameWithoutMarkup()
                );
                break;
            case ALLIANCE_JOBS_DIPLOMATIC:
                if ($userId === $playerId) {
                    throw new AccessViolation();
                }
                $alliance->setDiplomatic($playerId);
                $text = sprintf(
                    'Du wurdest zum neuen Außenminister der Allianz %s ernannt',
                    $alliance->getNameWithoutMarkup()
                );
                break;
        }
        $alliance->truncateJobCache();

        PM::sendPM($userId, $playerId, $text);

        $game->addInformation(_('Das Mitglied wurde befördert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
