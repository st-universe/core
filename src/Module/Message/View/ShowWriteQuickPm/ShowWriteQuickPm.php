<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use InvalidArgumentException;
use request;
use JBBCode\Parser;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowWriteQuickPm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WRITE_QUICKPM';

    public const TYPE_USER = 1;
    public const TYPE_SHIP = 2;
    public const TYPE_FLEET = 3;
    public const TYPE_STATION = 4;
    public const TYPE_COLONY = 5;

    private Parser $bbCodeParser;

    private UserRepositoryInterface $userRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;


    public function __construct(
        Parser $bbCodeParser,
        UserRepositoryInterface $userRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->bbCodeParser = $bbCodeParser;
        $this->userRepository = $userRepository;
        $this->fleetRepository = $fleetRepository;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/write_quick_pm');
        $game->setPageTitle(_('Neue private Nachricht'));

        $fromId = request::getIntFatal('fromid');
        $toId =  request::getIntFatal('toid');
        $fromType = request::getIntFatal('fromtype');
        $toType = request::getIntFatal('totype');
        $rpgtext = '';

        $setTemplateText = true;

        switch ($fromType) {
            case self::TYPE_USER:
                $from = $this->userRepository->find($fromId);
                if ($from === null || $from !== $game->getUser()) {
                    return;
                }
                $setTemplateText = false;
                break;
            case self::TYPE_SHIP:
                $from = $this->shipRepository->find($fromId);
                if ($from === null || $from->getUser() !== $game->getUser()) {
                    return;
                }
                $whoText = _('Die');
                $sectorString = $from->getSectorString();
                break;
            case self::TYPE_FLEET:
                $from = $this->fleetRepository->find($fromId);
                if ($from === null || $from->getUser() !== $game->getUser()) {
                    return;
                }
                $whoText = _('Die Flotte');
                $sectorString = $from->getLeadShip()->getSectorString();
                break;
            case self::TYPE_STATION:
                $from = $this->shipRepository->find($fromId);
                if ($from === null || $from->getUser() !== $game->getUser()) {
                    return;
                }
                $whoText = _('Die Station');
                $sectorString = $from->getSectorString();
                break;
            case self::TYPE_COLONY:
                $from = $this->colonyRepository->find($fromId);
                if ($from === null || $from->getUser() !== $game->getUser()) {
                    return;
                }
                $whoText = _('Die Kolonie');
                $sectorString = $from->getSectorString();
                break;

            default:
                throw new InvalidArgumentException('fromtype has invalid value');
        }

        switch ($toType) {
            case self::TYPE_USER:
                $to = $this->userRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $recipient = $to;
                $setTemplateText = false;
                break;
            case self::TYPE_SHIP:
                $to = $this->shipRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $toText = _('der');
                $recipient = $to->getUser();
                break;
            case self::TYPE_FLEET:
                $to = $this->fleetRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $toText = _('der Flotte');
                $recipient = $to->getUser();
                break;
            case self::TYPE_STATION:
                $to = $this->shipRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $toText = _('der Station');
                $recipient = $to->getUser();
                break;
            case self::TYPE_COLONY:
                $to = $this->colonyRepository->find($toId);
                if ($to === null) {
                    return;
                }
                $toText = _('der Kolonie');
                $recipient = $to->getUser();
                break;

            default:
                throw new InvalidArgumentException('fromtype has invalid value');
        }

        switch ($recipient->getRpgBehavior()) {
            case 0:
                $rpgtext = 'Der Spieler hat seine Rollenspieleinstellung nicht gesetzt';
                break;
            case 1:
                $rpgtext = 'Der Spieler betreibt gerne Rollenspiel';
                break;
            case 2:
                $rpgtext = 'Der Spieler betreibt gelegentlich Rollenspiel';
                break;
            case 3:
                $rpgtext = 'Der Spieler betreibt ungern Rollenspiel';
                break;
        }

        $game->setTemplateVar('RPGTEXT', $rpgtext);
        $game->setTemplateVar('RECIPIENT', $recipient);
        $game->setTemplateVar(
            'TEMPLATETEXT',
            $setTemplateText ? sprintf(
                _('%s "%s" sendet %s "%s" in Sektor %s folgende Nachricht:'),
                $whoText,
                $this->bbCodeParser->parse($from->getName())->getAsText(),
                $toText,
                $this->bbCodeParser->parse($to->getName())->getAsText(),
                $sectorString
            ) : ''
        );
    }
}
