<?php

declare(strict_types=1);

namespace Stu\Module\Message\View\ShowWriteQuickPm;

use request;
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

    private UserRepositoryInterface $userRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        UserRepositoryInterface $userRepository,
        FleetRepositoryInterface $fleetRepository,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository
    ) {
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

        $setTemplateText = true;

        switch ($fromType) {
            case self::TYPE_USER:
                $from = $this->userRepository->find($fromId);
                $setTemplateText = false;
                break;
            case self::TYPE_SHIP:
                $from = $this->shipRepository->find($fromId);
                $whoText = _('Die');
                break;
            case self::TYPE_FLEET:
                $from = $this->fleetRepository->find($fromId);
                $whoText = _('Die Flotte');
                break;
            case self::TYPE_STATION:
                $from = $this->shipRepository->find($fromId);
                $whoText = _('Die Station');
                break;
            case self::TYPE_COLONY:
                $from = $this->colonyRepository->find($fromId);
                $whoText = _('Die Kolonie');
                break;
        }

        switch ($toType) {
            case self::TYPE_USER:
                $to = $this->userRepository->find($toId);
                $setTemplateText = false;
                break;
            case self::TYPE_SHIP:
                $to = $this->shipRepository->find($toId);
                $toText = _('der');
                break;
            case self::TYPE_FLEET:
                $to = $this->fleetRepository->find($toId);
                $toText = _('der Flotte');
                break;
            case self::TYPE_STATION:
                $to = $this->shipRepository->find($toId);
                $toText = _('der Station');
                break;
            case self::TYPE_COLONY:
                $to = $this->colonyRepository->find($toId);
                $toText = _('der Kolonie');
                break;
        }

        $game->setTemplateVar(
            'RECIPIENT',
            $this->userRepository->find($this->showWriteQuickPmRequest->getRecipientId())
        );
        $game->setTemplateVar(
            'TEMPLATETEXT',
            $setTemplateText ? sprintf(
                _('%s %s sendet %s %s folgende Nachricht:'),
                $whoText,
                $from->getName(),
                $toText,
                $to->getName()
            ) : ''
        );
    }
}
