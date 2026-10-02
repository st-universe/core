<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\RegenerateSystem;

use request;
use RuntimeException;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Module\Admin\View\Map\ShowSystem\ShowSystem;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\NamesRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class RegenerateSystem implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'REGENERATE_SYSTEM';

    public function __construct(private StarSystemRepositoryInterface $starSystemRepository, private NamesRepositoryInterface $namesRepository, private StarSystemCreationInterface $starSystemCreation) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSystem::VIEW_IDENTIFIER);

        $systemId = request::getInt('systemid');

        $starSystem = $this->starSystemRepository->find($systemId);
        if ($starSystem === null) {
            return;
        }

        $map = $starSystem->getMap();
        if ($map === null) {
            return;
        }

        $layer = $map->getLayer();
        if ($layer === null) {
            throw new RuntimeException('this should not happen');
        }

        if ($layer->isFinished()) {
            $context->getInfo()->addInformation('Der Layer ist fertig, kein Neugenerierung mehr möglich');
            return;
        }

        $systemName = current($this->namesRepository->getRandomFreeSystemNames(1));
        if ($systemName === false) {
            throw new RuntimeException('no free system name available');
        }

        $this->starSystemCreation->recreateStarSystem($map, $systemName->getName());

        $context->getInfo()->addInformation('Das System wurde neu generiert.');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
