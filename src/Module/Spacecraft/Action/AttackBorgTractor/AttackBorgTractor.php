<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AttackBorgTractor;

use Override;
use request;
use Stu\Component\Borg\BorgTractorRescue;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class AttackBorgTractor implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ATTACK_BORG_TRACTOR';

    public function __construct(private ShipLoaderInterface $ships, private BorgTractorRescue $rescue) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $wrappers = $this->ships->getWrappersBySourceAndUserAndTarget(request::indInt('id'), $context->getUser()->getId(), request::indInt('target'));
        $target = $wrappers->getTarget();
        if ($target === null) {
            return;
        }
        $source = $wrappers->getSource();
        $report = new InformationWrapper();
        if (!$this->rescue->attack($source, $target, $report)) {
            $context->getInfo()->addInformationWrapper($report);
            return;
        }
        if ($source->get()->getCondition()->isDestroyed()) {
            $context->setView(ModuleEnum::SHIP);
            $context->getInfo()->addInformationWrapper($report);
            return;
        }
        $context->getInfo()->addInformation('Rettungsangriff durchgeführt');
        $context->setTemplateVar('FIGHT_RESULTS', $report->getInformations());
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
