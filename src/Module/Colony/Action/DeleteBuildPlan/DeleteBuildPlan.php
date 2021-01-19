<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\DeleteBuildPlan;

use Stu\Exception\AccessViolation;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class DeleteBuildPlan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_BUILDPLAN';

    private ColonyLoaderInterface $colonyLoader;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setPageTitle(_('Bauplan löschen'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/entity_not_available');

        /** @var ShipBuildplanInterface $plan */
        $plan = $this->shipBuildplanRepository->find((int) request::getIntFatal('planid'));
        if ($plan === null || $plan->getUserId() !== $userId || $plan->isDeleteable() === false) {
            throw new AccessViolation();
        }
        $this->buildplanModuleRepository->truncateByBuildplan($plan->getId());
        $this->shipBuildplanRepository->delete($plan);

        //$this->getTemplate()->setVar('FUNC', $this->getSelectedBuildingFunction());
        $game->showMacro('html/colonymacros.xhtml/cm_buildplan_deleted');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
