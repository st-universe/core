<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RotateBuilding;

use request;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\View\ShowField\ShowField;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class RotateBuilding implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ROTATE_BUILDING';

    public function __construct(
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private PlanetFieldRepositoryInterface $planetFieldRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $field = $this->planetFieldHostProvider->loadFieldViaRequestParameter($context->getUser());
        $context->setView(ShowField::VIEW_IDENTIFIER);

        $building = $field->getBuilding();
        if ($building === null || !$building->isRotatable() || $field->isUnderConstruction()) {
            return;
        }

        $rotation = match (request::postString('direction')) {
            'right' => 270,
            'left' => 90,
            default => null
        };

        if ($rotation === null) {
            return;
        }

        $field->setRotation(($field->getRotation() + $rotation) % 360);
        $this->planetFieldRepository->save($field);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
