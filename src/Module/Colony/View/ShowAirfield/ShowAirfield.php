<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowAirfield;

use ColonyMenu;
use Shiprump;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class ShowAirfield implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_AIRFIELD';

    private $colonyLoader;

    private $colonyGuiHelper;

    private $showAirfieldRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyGuiHelperInterface $colonyGuiHelper,
        ShowAirfieldRequestInterface $showAirfieldRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyGuiHelper = $colonyGuiHelper;
        $this->showAirfieldRequest = $showAirfieldRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showAirfieldRequest->getColonyId(),
            $userId
        );

        $this->colonyGuiHelper->register($colony, $game);

        $game->showMacro('html/colonymacros.xhtml/cm_airfield');

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MENU_SELECTOR', new ColonyMenu(MENU_AIRFIELD));
        $game->setTemplateVar(
            'STARTABLE_SHIPS',
            Shiprump::getBy(
                sprintf(
                    "WHERE id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id = %d) AND good_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id = %d) GROUP BY id",
                    $userId,
                    $colony->getId()
                )
            )
        );
        $game->setTemplateVar(
            'BUILDABLE_SHIPS',
            Shiprump::getBuildableRumpsByBuildingFunction($userId,BUILDING_FUNCTION_AIRFIELD)
        );
    }
}
