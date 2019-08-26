<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use AccessViolation;
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
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $plan = new ShipBuildplans(request::getIntFatal('planid'));
        if ($plan->getUserId() != $userId) {
            throw new AccessViolation();
        }
        $plan->delete();

        $game->setTemplateFile('html/ajaxempty.xhtml');
        //$this->getTemplate()->setVar('FUNC', $this->getSelectedBuildingFunction());
        $game->setAjaxMacro('html/colonymacros.xhtml/cm_buildplans');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
