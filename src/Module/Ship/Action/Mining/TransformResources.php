<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Mining;

use Override;
use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;


final class TransformResources implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFORM_RESOURCES';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $ship = $wrapper->get();
        $aggregationsystem = $wrapper->getAggregationSystemSystemData();

        if ($aggregationsystem === null) {
            throw new SanityCheckException('collector = null ', self::ACTION_IDENTIFIER);
        }

        $commodityId = request::postInt('chosen');

        if ($commodityId === 0) {
            if ($ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM)) {
                $this->helper->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM, $game);
                $aggregationsystem->setCommodityId($commodityId)->update();
            }
            return;
        } else {


            if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_AGGREGATION_SYSTEM, $game)) {
                return;
            }
            $aggregationsystem->setCommodityId($commodityId)->update();
            $game->addInformationf(
                "Ressourcen werden umgewandelt",
            );
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}