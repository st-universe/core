<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use request;
use Stu\Component\Borg\BorgSphereSpawner;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class SpawnBorgSphere implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SPAWN_BORG_SPHERE';

    public function __construct(private BorgSphereSpawner $spawner) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowScripts::VIEW_IDENTIFIER);
        $this->spawner->spawn(request::postIntFatal('borg_configuration_id'), $context->getInfo());
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
