<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\PromotePlayer;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\View\Management\Management;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class PromotePlayer implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_PROMOTE_USER';

    public function __construct(private PromotePlayerRequestInterface $promotePlayerRequest, private AllianceJobRepositoryInterface $allianceJobRepository, private AllianceActionManagerInterface $allianceActionManager, private PrivateMessageSenderInterface $privateMessageSender, private UserRepositoryInterface $userRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceActionManager->mayEdit($alliance, $user)) {
            throw new AccessViolation();
        }

        $playerId = $this->promotePlayerRequest->getPlayerId();

        $player = $this->userRepository->find($playerId);

        if ($player === null || $player->getAlliance() !== $alliance) {
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

        if ($alliance->getFounder()->getUserId() === $playerId) {
            throw new AccessViolation();
        }

        $this->allianceJobRepository->truncateByUser($playerId);
        $alliance->getJobs()->remove($type);

        $text = '';
        $view = Management::VIEW_IDENTIFIER;

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
                    $playerId,
                    AllianceEnum::ALLIANCE_JOBS_FOUNDER
                );
                $text = sprintf(
                    _('Du wurdest zum neuen Präsidenten der Allianz %s ernannt'),
                    $alliance->getName()
                );
                $view = ModuleEnum::ALLIANCE;
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

        $game->setView($view);

        $game->addInformation(_('Das Mitglied wurde befördert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
