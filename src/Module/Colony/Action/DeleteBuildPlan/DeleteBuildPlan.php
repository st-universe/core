<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use request;
use ShipBuildplans;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $planid = request::getIntFatal('planid');
        $plan = new ShipBuildplans($planid);
        $plan->delete();

        $game->setTemplateFile('html/ajaxempty.xhtml');
        $game->setTemplateVar('currentColony', $colony);
        //$this->getTemplate()->setVar('FUNC', $this->getSelectedBuildingFunction());
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_buildplans');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
