<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\BorgAssimilateTarget;

use Override;
use request;
use Stu\Component\Borg\BorgAssimilationCapture;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgAssimilateTarget implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BORG_ASSIMILATE_TARGET';

    public function __construct(
        private ShipLoaderInterface $ships,
        private BorgSphereRepositoryInterface $events,
        private BorgAssimilationCapture $assimilation,
        private StuTime $time
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $userId = $context->getUser()->getId();
        $wrappers = $this->ships->getWrappersBySourceAndUserAndTarget(request::indInt('id'), $userId, request::indInt('target'));
        $source = $wrappers->getSource()->get();
        $target = $wrappers->getTarget()?->get();
        $event = $this->events->getByShip($source);
        if ($source->getUser()->getId() !== $userId || $event === null
            || $event->getConfiguration()->getUser()->getId() !== $userId) {
            $context->getInfo()->addInformation('Dieses Schiff kann keine Schiffe assimilieren');
            return;
        }
        $this->events->lockEvent($event);
        if (!$target instanceof Ship || !$event->getConfiguration()->isAssimilationEnabled()
            || !$event->getConfiguration()->isActive($this->time->time())
            || !$this->assimilation->captureTarget($event, $source, $target)) {
            $context->getInfo()->addInformation('Dieses Ziel kann momentan nicht assimiliert werden');
            return;
        }
        $context->getInfo()->addInformationf('Die Assimilation der %s wurde begonnen', $target->getName());
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
