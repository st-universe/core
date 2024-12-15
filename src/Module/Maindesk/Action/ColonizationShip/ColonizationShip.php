<?php

declare(strict_types=1);

namespace Stu\Module\Maindesk\Action\ColonizationShip;

use InvalidArgumentException;
use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonizationShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLONIZATION_SHIP';

    public const int FED_COL_BUILDPLAN = 2075;
    public const int ROM_COL_BUILDPLAN = 2076;
    public const int KLING_COL_BUILDPLAN = 2077;
    public const int CARD_COL_BUILDPLAN = 2078;
    public const int FERG_COL_BUILDPLAN = 2079;

    public function __construct(private ShipCreatorInterface $shipCreator, private UserRepositoryInterface $userRepository) {}

    #[Override]
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
        return SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_COLONIZER + $factionId;
    }

    private function getBuildplanId(int $factionId): int
    {
        if ($factionId == 1) {
            return self::FED_COL_BUILDPLAN;
        }

        if ($factionId == 2) {
            return self::ROM_COL_BUILDPLAN;
        }

        if ($factionId == 3) {
            return self::KLING_COL_BUILDPLAN;
        }

        if ($factionId == 4) {
            return self::CARD_COL_BUILDPLAN;
        }

        if ($factionId == 5) {
            return self::FERG_COL_BUILDPLAN;
        }

        throw new InvalidArgumentException('faction is not configured');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
