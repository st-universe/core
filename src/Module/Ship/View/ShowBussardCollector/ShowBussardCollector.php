<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBussardCollector;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\LocationMiningRepositoryInterface;

final class ShowBussardCollector implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BUSSARD_COLLECTOR_AJAX';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private LocationMiningRepositoryInterface $locationMiningRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $ship = $wrapper->get();

        $game->setPageTitle(_('Bussard-Kollektor'));
        $game->setMacroInAjaxWindow('html/ship/bussardcollector.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);

        $collector = $wrapper->getBussardCollectorSystemData();
        if ($collector === null) {
            throw new SanityCheckException('no bussard collector installed', null, self::VIEW_IDENTIFIER);
        }

        $mining = $this->locationMiningRepository->getMiningAtLocation($ship);
        $miningqueue = $ship->getMiningQueue();
        $game->setTemplateVar('MINING', $mining);
        $game->setTemplateVar('MININGQUEUE', $miningqueue);
    }
}
