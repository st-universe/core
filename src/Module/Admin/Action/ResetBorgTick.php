<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use request;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class ResetBorgTick implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RESET_BORG_TICK';

    public function __construct(private BorgSphereRepositoryInterface $events, private StuTime $time) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowScripts::VIEW_IDENTIFIER);
        $configuration = $this->events->findConfiguration(request::postIntFatal('borg_configuration_id'));
        if ($configuration === null) {
            $context->getInfo()->addInformation('Die Borg-Konfiguration wurde nicht gefunden');
            return;
        }
        $event = $this->events->getByConfiguration($configuration);
        if ($event === null) {
            $context->getInfo()->addInformation('Das Borg-Schiff dieser Konfiguration wurde noch nicht gespawnt');
            return;
        }
        $this->events->lockEvent($event);
        if ($event->isFinished() || $event->getShipId() === null) {
            $context->getInfo()->addInformation('Dieses Borg-Event ist bereits beendet');
            return;
        }
        $now = $this->time->time();
        $interval = $configuration->getSettings()['tick_seconds'] ?? 0;
        if (!$configuration->isActive($now) || $interval < 1) {
            $context->getInfo()->addInformation('Für dieses Borg-Event ist keine gültige aktive Konfiguration vorhanden');
            return;
        }
        $nextTick = $now + $interval;
        $event->setNextTickAt($nextTick);
        $this->events->save($event);
        $context->getInfo()->addInformation(sprintf('Nächste Borg-Aktion: %s Uhr', $this->time->transformToStuDateTime($nextTick)));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
