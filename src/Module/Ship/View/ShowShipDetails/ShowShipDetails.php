<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShipDetails;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowShipDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPDETAILS';

    public function __construct(private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $game->setPageTitle(_('Schiffsinformationen'));
        $game->setMacroInAjaxWindow('html/ship/shipDetails.twig');

        $game->setTemplateVar('WRAPPER', $wrapper);
    }
}
