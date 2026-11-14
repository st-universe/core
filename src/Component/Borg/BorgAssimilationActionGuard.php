<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\EntityLockedException;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Module\Game\Action\Transfer\Transfer;
use Stu\Module\Ship\Action\LandShuttle\LandShuttle;
use Stu\Module\Spacecraft\Action\ActivateTractorBeam\ActivateTractorBeam;
use Stu\Module\Spacecraft\Action\BoardShip\BoardShip;
use Stu\Module\Spacecraft\Action\PromoteCrew\PromoteCrew;
use Stu\Module\Spacecraft\Action\RenameCrewman\RenameCrewman;
use Stu\Module\Spacecraft\Action\StartTakeover\StartTakeover;
use Stu\Module\Spacecraft\Action\StoreShuttle\StoreShuttle;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class BorgAssimilationActionGuard
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecrafts,
        private CrewAssignmentRepositoryInterface $assignments
    ) {}

    public function check(ModuleEnum $module, string $action, User $user): void
    {
        if (in_array($module, [ModuleEnum::ADMIN, ModuleEnum::NPC], true)) {
            return;
        }
        if ($module === ModuleEnum::COLONY) {
            $this->checkShipId(request::indInt('ship_id'), $user->getId());
            $this->checkShipId(request::indInt('shipid'), $user->getId());
            $this->checkSelection('spacecrafts', $user->getId());
            return;
        }
        if ($action === Transfer::ACTION_IDENTIFIER) {
            $this->checkTransfer($user->getId());
            return;
        }
        if (!in_array($module, [ModuleEnum::SHIP, ModuleEnum::STATION], true)) {
            return;
        }
        if (in_array($action, [RenameCrewman::ACTION_IDENTIFIER, PromoteCrew::ACTION_IDENTIFIER], true)) {
            $assignment = $this->assignments->findOneBy(['crew' => request::indInt('id')]);
            $ship = $assignment?->getSpacecraft();
            if ($ship !== null) {
                self::assertUsable($ship, $user->getId());
            }
            return;
        }
        foreach (['id', 'shipid', 'shuttle'] as $parameter) {
            $id = request::indInt($parameter);
            if ($id > 0) {
                $this->checkOwned($this->spacecrafts->find($id), $user);
            }
        }
        $this->checkInteractionTarget($action, $user->getId());
        foreach (['chosen', 'spacecrafts', 'ships', 'shipids'] as $parameter) {
            $this->checkSelection($parameter, $user->getId());
        }
    }

    private function checkInteractionTarget(string $action, int $userId): void
    {
        if (in_array($action, [ActivateTractorBeam::ACTION_IDENTIFIER, BoardShip::ACTION_IDENTIFIER, StartTakeover::ACTION_IDENTIFIER, StoreShuttle::ACTION_IDENTIFIER, LandShuttle::ACTION_IDENTIFIER], true)) {
            $this->checkShipId(request::indInt($action === LandShuttle::ACTION_IDENTIFIER ? 'id' : 'target'), $userId);
        }
    }

    private function checkSelection(string $parameter, int $userId): void
    {
        foreach (request::postArray($parameter) as $id) {
            if (is_scalar($id)) {
                $this->checkShipId((int) $id, $userId);
            }
        }
    }

    private function checkTransfer(int $userId): void
    {
        foreach (['id' => 'source_type', 'target' => 'target_type'] as $parameter => $type) {
            if (in_array(request::postString($type), [TransferEntityTypeEnum::SHIP->value, TransferEntityTypeEnum::STATION->value], true)) {
                $this->checkShipId(request::postInt($parameter), $userId);
            }
        }
    }

    private function checkShipId(int $id, int $userId): void
    {
        $ship = $id > 0 ? $this->spacecrafts->find($id) : null;
        if ($ship !== null) {
            self::assertUsable($ship, $userId);
        }
    }

    private function checkOwned(?Spacecraft $spacecraft, User $user): void
    {
        if ($spacecraft?->getUser()->getId() === $user->getId()) {
            self::assertUsable($spacecraft, $user->getId());
        }
    }

    public static function assertUsable(Spacecraft $spacecraft, ?int $controllingUserId = null): void
    {
        if ($spacecraft->isBeingAssimilated()) {
            throw new EntityLockedException($controllingUserId !== null && $controllingUserId === $spacecraft->getUser()->getId()
                ? 'Das Schiff wird assimiliert und kann bis zu seiner Befreiung nicht gesteuert oder verändert werden'
                : 'Mit dem Schiff kann während der Assimilation nicht interagiert werden');
        }
    }
}
