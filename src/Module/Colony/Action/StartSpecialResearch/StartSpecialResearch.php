<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartSpecialResearch;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\SpecialResearchFacility;
use Stu\Component\Research\SpecialResearchService;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;

final class StartSpecialResearch implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_SPECIAL_RESEARCH';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private SpecialResearchService $research, private SpecialResearchFacility $facility) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_SPECIAL_RESEARCH);
        $colony = $this->colonyLoader->loadWithOwnerValidation(request::postIntFatal('id'), $context->getUser()->getId());
        $type = AwayMissionResearchEnum::tryFrom(request::postStringFatal('research_type'));
        if ($type === null) {
            $context->getInfo()->addInformation('Diese Sonderforschung existiert nicht');
            return;
        }
        $fieldId = request::postIntFatal('fid');
        $field = $fieldId > 0 ? $this->facility->find($colony, $fieldId) : null;
        if ($field === null) {
            $context->getInfo()->addInformation('Das ausgewählte Forschungsgebäude ist nicht vorhanden');
            return;
        }
        $this->research->start($colony, $field, $type, $context->getInfo());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
