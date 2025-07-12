<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\OpenAdventDoor;

use Override;
use request;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\OpenedAdventDoorRepositoryInterface;

final class OpenAdventDoor implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADVENT_DOOR';

    private const int NICHOLAS_AMOUNT = 500;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AnomalyRepositoryInterface $anomalyRepository,
        private OpenedAdventDoorRepositoryInterface $openedAdventDoorRepository,
        private CommodityCacheInterface $commodityCache,
        private StorageManagerInterface $storageManager,
        private CreatePrestigeLogInterface $createPrestigeLog
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();

        $anomaly = $this->anomalyRepository->find(request::getIntFatal('target'));
        if ($anomaly === null) {
            throw new SanityCheckException(sprintf('anomaly with id %d does not exist', request::getIntFatal('target')), self::ACTION_IDENTIFIER);
        }

        if ($anomaly->getAnomalyType()->getId() !== AnomalyTypeEnum::SPECIAL_ADVENT_DOOR->value) {
            throw new SanityCheckException('target is not an advent door', self::ACTION_IDENTIFIER);
        }
        if ($anomaly->getLocation() !== $ship->getLocation()) {
            throw new SanityCheckException('can not interact with target', self::ACTION_IDENTIFIER);
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        $openedDoors = $this->openedAdventDoorRepository->getOpenedDoorsCountOfToday($user);

        //check for nicholas present
        if ((int)date("j") === 6 && $openedDoors === 1) {
            $this->nicholasPresent($game);
            $this->createOpenedAdventDoor($user);
            return;
        } elseif ($openedDoors > 0) {
            $game->addInformation("Du hast heute bereits ein Türchen geöffnet");
            return;
        }

        if ($ship->getStorageSum() === $ship->getMaxStorage()) {
            $game->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $ship->getName()));
            return;
        }

        $this->createOpenedAdventDoor($user);

        $commodity = $this->commodityCache->get(CommodityTypeConstants::COMMODITY_ADVENT_POINT);
        $this->storageManager->upperStorage($ship, $commodity, 1);

        $game->addInformation(sprintf('1 %s wurde in den Frachtraum deines Schiffes transferiert', $commodity->getName()));
    }

    private function nicholasPresent(GameControllerInterface $game): void
    {
        $msg = sprintf('%d Prestige vom Nikolaus erhalten', self::NICHOLAS_AMOUNT);

        $this->createPrestigeLog->createLog(
            self::NICHOLAS_AMOUNT,
            $msg,
            $game->getUser(),
            time()
        );

        $game->addInformation("Du hast " . $msg);
    }

    private function createOpenedAdventDoor(User $user): void
    {
        $openedDoor = $this->openedAdventDoorRepository->prototype();
        $openedDoor
            ->setUserId($user->getId())
            ->setDay((int)date("j"))
            ->setYear((int)date("Y"))
            ->setTime(time());

        $this->openedAdventDoorRepository->save($openedDoor);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
