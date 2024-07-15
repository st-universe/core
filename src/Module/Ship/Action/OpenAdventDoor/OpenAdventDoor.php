<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\OpenAdventDoor;

use Override;
use request;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Ship\Storage\ShipStorageManager;
use Stu\Exception\SanityCheckException;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Commodity\Lib\CommodityCacheInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\OpenedAdventDoorRepositoryInterface;

final class OpenAdventDoor implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADVENT_DOOR';

    private const int NICHOLAS_AMOUNT = 500;

    public function __construct(private ShipLoaderInterface $shipLoader, private AnomalyRepositoryInterface $anomalyRepository, private OpenedAdventDoorRepositoryInterface $openedAdventDoorRepository, private CommodityCacheInterface $commodityCache, private ShipStorageManager $shipStorageManager, private CreatePrestigeLogInterface $createPrestigeLog)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
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

        $commodity = $this->commodityCache->get(CommodityTypeEnum::COMMODITY_ADVENT_POINT);
        $this->shipStorageManager->upperStorage($ship, $commodity, 1);

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

    private function createOpenedAdventDoor(UserInterface $user): void
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
