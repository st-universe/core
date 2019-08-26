<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowField;

use Colfields;
use ColonyShipQueue;
use request;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Orm\Entity\ColonyShipRepairInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;

final class ShowField implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_FIELD';

    private $colonyLoader;

    private $colonyShipRepairRepository;

    private $showFieldRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ShowFieldRequestInterface $showFieldRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->showFieldRequest = $showFieldRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showFieldRequest->getColonyId(),
            $userId
        );
        $fieldId = (int)request::indInt('fid');

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

        $game->setPageTitle(sprintf('Feld %d - Informationen', $field->getId()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/colonymacros.xhtml/fieldaction');

        $game->setTemplateVar('FIELD', $field);
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('SHIP_BUILD_PROGRESS', ColonyShipQueue::getByColonyId($colony->getId()));
        $game->setTemplateVar('SHIP_REPAIR_PROGRESS', $shipRepairProgress);
    }
}
