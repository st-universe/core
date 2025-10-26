<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AddWormholeRestriction;

use request;
use Stu\Component\Ship\Wormhole\WormholeEntryModeEnum;
use Stu\Component\Ship\Wormhole\WormholeEntryTypeEnum as RestrictionTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\View\ShowWormholeRestrictions\ShowWormholeRestrictions;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\Orm\Repository\WormholeRestrictionRepositoryInterface;

final class AddWormholeRestriction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_WORMHOLE_RESTRICTION';
    public function __construct(
        private FactionRepositoryInterface $factionRepository,
        private WormholeRestrictionRepositoryInterface $wormholeRestrictionRepository,
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private AllianceRepositoryInterface $allianceRepository,
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {

        if (!request::has('entryId')) {
            return;
        }
        $entryId = request::getIntFatal('entryId');
        $target = request::getIntFatal('target');
        $typeId = request::getIntFatal('type');
        $modeId = request::getIntFatal('mode');

        $type = RestrictionTypeEnum::from($typeId);
        $mode = WormholeEntryModeEnum::from($modeId);

        $game->setView(ShowWormholeRestrictions::VIEW_IDENTIFIER);

        $wormholeEntry = $this->wormholeEntryRepository->find($entryId);
        if ($wormholeEntry === null) {
            return;
        }

        if ($this->wormholeRestrictionRepository->existsForTargetAndTypeAndEntry($target, $type, $wormholeEntry)) {
            return;
        }

        $targetEntity = match ($type) {
            RestrictionTypeEnum::USER => $this->userRepository->find($target),
            RestrictionTypeEnum::ALLIANCE => $this->allianceRepository->find($target),
            RestrictionTypeEnum::FACTION => $this->factionRepository->find($target),
            RestrictionTypeEnum::SHIP => null
        };

        if ($targetEntity !== null || ($type == RestrictionTypeEnum::FACTION && $target >= 1 && $target <= 5)) {
            $restriction = $this->wormholeRestrictionRepository->prototype();
            $restriction->setWormholeEntry($wormholeEntry);
            $restriction->setTargetId($target);
            $restriction->setPrivilegeType($type);
            $restriction->setMode($mode->value);

            $this->wormholeRestrictionRepository->save($restriction);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
