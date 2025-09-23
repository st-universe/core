<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowWormholeRestrictions;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftUiFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;

final class ShowWormholeRestrictions implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_WORMHOLE_RESTRICTIONS';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private SpacecraftLoaderInterface $spacecraftLoader,
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

        $entryId = request::getIntFatal('entryId');
        $entry = $this->wormholeEntryRepository->find($entryId);

        $game->showMacro('html/spacecraft/wormholePrivileges.twig');

        $restrictions = [];
        if ($entry !== null) {
            $restrictionList = [];
            foreach ($entry->getRestrictions() as $restriction) {
                $restrictionList[] = $this->spacecraftUiFactory->createWormholeRestrictionItem($restriction);
            }
            $restrictions = $restrictionList;
        }

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('ENTRY', $entry);
        $game->setTemplateVar('ENTRY_ID', $entryId);
        $game->setTemplateVar('RESTRICTIONS', $restrictions);
    }
}
