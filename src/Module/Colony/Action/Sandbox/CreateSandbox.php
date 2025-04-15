<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Sandbox;

use Override;
use request;
use Stu\Module\Colony\View\Sandbox\ShowColonySandbox;
use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CreateSandbox implements
    ActionControllerInterface,
    AccessCheckControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_SANDBOX';

    public function __construct(
        private ColonySandboxRepositoryInterface $colonySandboxRepository,
        private PlanetFieldRepositoryInterface $planetFieldRepository
    ) {}

    #[Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::COLONY_SANDBOX;
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColonySandbox::VIEW_IDENTIFIER);

        $colonyId = request::getIntFatal('cid');

        /** @var ColonyInterface|null */
        $colony = $game->getUser()->getColonies()->get($colonyId);
        if ($colony === null) {
            return;
        }

        $sandboxName = request::getStringFatal('name');

        $sandbox = $this->colonySandboxRepository->prototype();
        $sandbox
            ->setColony($colony)
            ->setName($sandboxName)
            ->setMaxBev($colony->getMaxBev())
            ->setMaxEps($colony->getMaxEps())
            ->setMaxStorage($colony->getMaxStorage())
            ->setWorkers($colony->getWorkers())
            ->setMask($colony->getMask());
        $this->colonySandboxRepository->save($sandbox);

        foreach ($colony->getPlanetFields() as $fieldId => $field) {
            $building = $field->getBuilding();

            $sandboxField = $this->planetFieldRepository->prototype();
            $sandboxField
                ->setColonySandbox($sandbox)
                ->setFieldId($fieldId)
                ->setBuilding($building)
                ->setIntegrity($building === null ? 0 : $building->getIntegrity())
                ->setFieldType($field->getFieldType())
                ->setActive($field->getActive());

            $this->planetFieldRepository->save($sandboxField);

            $sandbox->getPlanetFields()->set($fieldId, $sandboxField);
        }

        $game->addInformationf(_('Sandbox %s wurde erstellt'), $sandboxName);

        $game->setViewContext(ViewContextTypeEnum::HOST, $sandbox);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
