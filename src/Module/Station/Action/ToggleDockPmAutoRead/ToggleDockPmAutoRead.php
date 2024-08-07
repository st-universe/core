<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\ToggleDockPmAutoRead;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\Noop\Noop;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ToggleDockPmAutoRead implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DOCK_PM_AUTO_READ';

    public function __construct(private ShipLoaderInterface $shipLoader, private TradePostRepositoryInterface $tradePostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(Noop::VIEW_IDENTIFIER);

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $tradePost = $wrapper->get()->getTradePost();
        if ($tradePost === null) {
            throw new SanityCheckException(
                sprintf(
                    'stationId %d is not a tradepost!',
                    request::indInt('id')
                ),
                self::ACTION_IDENTIFIER
            );
        }

        $currentValue = $tradePost->isDockPmAutoRead();
        $tradePost->setIsDockPmAutoRead(!$currentValue);
        $this->tradePostRepository->save($tradePost);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
