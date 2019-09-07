<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Colfields;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIELD';

    private $colonyLoader;

    private $colonyShipRepairRepository;

    private $showFieldRequest;

    private $colonyShipQueueRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShowFieldRequestInterface $showFieldRequest,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->showFieldRequest = $showFieldRequest;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFieldRequest->getColonyId(),
            $userId
        );
        $field = Colfields::getByColonyField(
            $this->showFieldRequest->getFieldId(),
            $colony->getId()
        );

        $shipRepairProgress = $this->colonyShipRepairRepository->getByColonyField(
            (int) $colony->getId(),
            (int) $field->getFieldId()
        );

        usort(
            $shipRepairProgress,
            function (ColonyShipRepairInterface $a, ColonyShipRepairInterface $b): int {
                return $a->getId() <=> $b->getId();
            }
        );

        $game->setPageTitle(sprintf('Feld %d - Informationen', $field->getFieldId()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/colonymacros.xhtml/fieldaction');

        $game->setTemplateVar('FIELD', $field);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('SHIP_BUILD_PROGRESS', $this->colonyShipQueueRepository->getByColony((int) $colony->getId()));
        $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
    }
}
