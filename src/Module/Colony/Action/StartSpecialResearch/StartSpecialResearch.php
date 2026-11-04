<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartSpecialResearch;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\SpecialResearchService;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;

final class StartSpecialResearch implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_SPECIAL_RESEARCH';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private SpecialResearchService $research) {}

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
        $this->research->start($colony, $type, $context->getInfo());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
