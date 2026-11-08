<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\StartTechnology;

use Override;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Research\SpecialResearchFacility;
use Stu\Component\Research\TechnologyService;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;

final class StartTechnology implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_TECHNOLOGY';

    public function __construct(
        private ColonyLoaderInterface $colonies,
        private SpecialResearchFacility $facility,
        private TechnologyRepositoryInterface $recipes,
        private ModuleRepositoryInterface $modules,
        private TechnologyService $service
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_SPECIAL_RESEARCH);
        $colony = $this->colonies->loadWithOwnerValidation(request::postIntFatal('id'), $context->getUser()->getId());
        $fieldId = request::postIntFatal('fid');
        $field = $fieldId > 0 ? $this->facility->find($colony, $fieldId) : null;
        $recipe = $this->recipes->getRecipe(request::postIntFatal('recipe_id'));
        if ($field === null || $recipe === null) {
            $context->getInfo()->addInformation('Das gewählte Verfahren oder Forschungsgebäude ist nicht vorhanden');
            return;
        }
        $module = request::postInt('module_id') > 0 ? $this->modules->find(request::postInt('module_id')) : null;
        $this->service->start($colony, $field, $recipe, $context->getInfo(), $module, request::postInt('module_count'), request::postInt('nanite_count'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
