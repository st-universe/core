<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Sandbox;

use request;
use Stu\Module\Admin\View\Sandbox\ShowColonySandbox;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class CreateSandbox implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_SANDBOX';

    private ColonySandboxRepositoryInterface $colonySandboxRepository;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        ColonySandboxRepositoryInterface $colonySandboxRepository,
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->colonySandboxRepository = $colonySandboxRepository;
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowColonySandbox::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $colonyId = request::postIntFatal('cid');

        /** @var ColonyInterface|null */
        $colony = $game->getUser()->getColonies()->get($colonyId);
        if ($colony === null) {
            return;
        }

        $sandboxName = request::postStringFatal('name');

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

        $game->setView(ShowColonySandbox::VIEW_IDENTIFIER, ['HOST' => $sandbox]);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
