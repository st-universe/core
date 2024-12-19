<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use InvalidArgumentException;
use JBBCode\Parser;
use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowWriteQuickPm implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WRITE_QUICKPM';

    public const int TYPE_USER = 1;
    public const int TYPE_SHIP = 2;
    public const int TYPE_FLEET = 3;
    public const int TYPE_STATION = 4;
    public const int TYPE_COLONY = 5;


    public function __construct(
        private Parser $bbCodeParser,
        private UserRepositoryInterface $userRepository,
        private FleetRepositoryInterface $fleetRepository,
        private ShipRepositoryInterface $shipRepository,
        private StationRepositoryInterface $stationRepository,
        private ColonyRepositoryInterface $colonyRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setMacroInAjaxWindow('html/communication/writeQuickPm.twig');
        $game->setPageTitle(_('Neue private Nachricht'));

        $fromId = request::getIntFatal('fromid');
        $toId =  request::getIntFatal('toid');
        $fromType = request::getIntFatal('fromtype');
        $toType = request::getIntFatal('totype');

        $conversationInfo = $this->determineFrom($fromId, $fromType, $game->getUser());

        if ($conversationInfo === null) {
            return;
        }

        $this->determineTo($toId, $toType, $conversationInfo);

        $recipient = $conversationInfo->getRecipient();

        $game->setTemplateVar('RECIPIENT', $recipient);
        $game->setTemplateVar('TEMPLATETEXT', $conversationInfo->getTemplateText($this->bbCodeParser));
    }

    private function determineFrom(int $fromId, int $fromType, UserInterface $user): ?ConversationInfo
    {
        $conversationInfo = new ConversationInfo();

        switch ($fromType) {
            case self::TYPE_USER:
                $from = $this->userRepository->find($fromId);
                if ($from === null || $from !== $user) {
                    return null;
                }
                $conversationInfo->setFrom($from);
                $conversationInfo->setShowTemplateText(false);
                break;
            case self::TYPE_SHIP:
                $from = $this->shipRepository->find($fromId);
                if ($from === null || $from->getUser() !== $user) {
                    return null;
                }
                $conversationInfo->setWhoText(_('Die'));
                $conversationInfo->setFrom($from);
                $conversationInfo->setSectorString($from->getSectorString());
                break;
            case self::TYPE_FLEET:
                $from = $this->fleetRepository->find($fromId);
                if ($from === null || $from->getUser() !== $user) {
                    return null;
                }
                $conversationInfo->setWhoText(_('Die Flotte'));
                $conversationInfo->setFrom($from);
                $conversationInfo->setSectorString($from->getLeadShip()->getSectorString());
                break;
            case self::TYPE_STATION:
                $from = $this->stationRepository->find($fromId);
                if ($from === null || $from->getUser() !== $user) {
                    return null;
                }
                $conversationInfo->setWhoText(_('Die Station'));
                $conversationInfo->setFrom($from);
                $conversationInfo->setSectorString($from->getSectorString());
                break;
            case self::TYPE_COLONY:
                $from = $this->colonyRepository->find($fromId);
                if ($from === null || $from->getUser() !== $user) {
                    return null;
                }
                $conversationInfo->setWhoText(_('Die Kolonie'));
                $conversationInfo->setFrom($from);
                $conversationInfo->setSectorString($from->getSectorString());
                break;

            default:
                throw new InvalidArgumentException('fromtype has invalid value');
        }

        return $conversationInfo;
    }

    private function determineTo(int $toId, int $toType, ConversationInfo $conversationInfo): void
    {
        switch ($toType) {
            case self::TYPE_USER:
                $to = $this->userRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $conversationInfo->setRecipient($to);
                $conversationInfo->setShowTemplateText(false);
                break;
            case self::TYPE_SHIP:
                $to = $this->shipRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $conversationInfo->setToText(_('der'));
                $conversationInfo->setTo($to);
                $conversationInfo->setRecipient($to->getUser());
                break;
            case self::TYPE_FLEET:
                $to = $this->fleetRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $conversationInfo->setTo($to);
                $conversationInfo->setToText(_('der Flotte'));
                $conversationInfo->setRecipient($to->getUser());
                break;
            case self::TYPE_STATION:
                $to = $this->stationRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $conversationInfo->setTo($to);
                $conversationInfo->setToText(_('der Station'));
                $conversationInfo->setRecipient($to->getUser());
                break;
            case self::TYPE_COLONY:
                $to = $this->colonyRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $conversationInfo->setTo($to);
                $conversationInfo->setToText(_('der Kolonie'));
                $conversationInfo->setRecipient($to->getUser());
                break;

            default:
                throw new InvalidArgumentException('fromtype has invalid value');
        }
    }
}
