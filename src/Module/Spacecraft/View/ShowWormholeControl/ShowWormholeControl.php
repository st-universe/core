<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowWormholeControl;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftUiFactoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Component\Map\WormholeEntryTypeEnum;

final class ShowWormholeControl implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WORMHOLE_CONTROL';
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private AllianceRepositoryInterface $allianceRepository,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private SpacecraftUiFactoryInterface $spacecraftUiFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $location = $ship->getLocation();
        $wormholeEntries = $this->wormholeEntryRepository->findBy(['map' => $location]) ?:
            $this->wormholeEntryRepository->findBy(['systemMap' => $location]);

        $restrictionItemsMap = [];
        foreach ($wormholeEntries as $entry) {
            $restrictionItems = [];
            foreach ($entry->getRestrictions() as $restriction) {
                $restrictionItems[] = $this->spacecraftUiFactory->createWormholeRestrictionItem($restriction);
            }
            $restrictionItemsMap[$entry->getId()] = $restrictionItems;
        }
        $game->setTemplateVar('RESTRICTION_ITEMS_MAP', $restrictionItemsMap);


        $game->setPageTitle(_('Wurmlochkontrolle'));
        $game->setMacroInAjaxWindow('html/spacecraft/wormholeControl.twig');
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('WORMHOLE_ENTRIES', $wormholeEntries);
        $game->setTemplateVar('DIRECTION_TYPES', [
            'BOTH' => 'Beide Richtungen',
            'MAP_TO_W' => 'Nur Eingang',
            'W_TO_MAP' => 'Nur Ausgang'
        ]);

        $game->setTemplateVar('RESTRICTION_TYPES', [
            1 => 'Spieler',
            2 => 'Allianz',
            3 => 'Rasse',
            4 => 'Schiff'
        ]);
    }
}
