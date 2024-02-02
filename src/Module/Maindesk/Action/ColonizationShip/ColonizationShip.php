<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\ColonizationShip;

use InvalidArgumentException;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonizationShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONIZATION_SHIP';

    private ShipCreatorInterface $shipCreator;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ShipCreatorInterface $shipCreator,
        UserRepositoryInterface $userRepository
    ) {
        $this->shipCreator = $shipCreator;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        if ($user->getState() !== 1) {
            throw new AccessViolation();
        }

        $faction = $user->getFaction();

        $startMap = $faction->getStartMap();
        if ($startMap === null) {
            throw new RuntimeException('faction has no start map');
        }

        $wrapper = $this->shipCreator->createBy(
            $user->getId(),
            $this->getRumpId($faction->getId()),
            $this->getBuildplanId($faction->getId())
        )
            ->setLocation($startMap)
            ->finishConfiguration();

        $ship = $wrapper->get();

        $ship->setSensorRange(5);

        $reactor = $wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor
                ->setOutput((int)floor($reactor->getOutput() * 5))
                ->setLoad($reactor->getCapacity());
        }

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null) {
            throw new RuntimeException('no eps installed');
        }

        $eps->setEps((int)floor($eps->getTheoreticalMaxEps() * 5))->update();
        $eps->setMaxEps((int)floor($eps->getTheoreticalMaxEps() * 5))->update();

        $warpdrive = $wrapper->getWarpDriveSystemData();
        if ($warpdrive !== null) {
            $warpdrive->setWarpDrive((int)floor($warpdrive->getMaxWarpdrive() * 5))->update();
            $warpdrive->setMaxWarpDrive((int)floor($warpdrive->getMaxWarpdrive() * 5))->update();
        }

        $user->setState(UserEnum::USER_STATE_COLONIZATION_SHIP);
        $this->userRepository->save($user);

        $game->redirectTo('./ship.php');
    }

    private function getRumpId(int $factionId): int
    {
        if ($factionId == 1) {
            return ShipEnum::FED_COL_RUMP;
        }

        if ($factionId == 2) {
            return ShipEnum::ROM_COL_RUMP;
        }

        if ($factionId == 3) {
            return ShipEnum::KLING_COL_RUMP;
        }

        if ($factionId == 4) {
            return ShipEnum::CARD_COL_RUMP;
        }

        if ($factionId == 5) {
            return ShipEnum::FERG_COL_RUMP;
        }

        throw new InvalidArgumentException('faction is not configured');
    }

    private function getBuildplanId(int $factionId): int
    {
        if ($factionId == 1) {
            return ShipEnum::FED_COL_BUILDPLAN;
        }

        if ($factionId == 2) {
            return ShipEnum::ROM_COL_BUILDPLAN;
        }

        if ($factionId == 3) {
            return ShipEnum::KLING_COL_BUILDPLAN;
        }

        if ($factionId == 4) {
            return ShipEnum::CARD_COL_BUILDPLAN;
        }

        if ($factionId == 5) {
            return ShipEnum::FERG_COL_BUILDPLAN;
        }

        throw new InvalidArgumentException('faction is not configured');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
