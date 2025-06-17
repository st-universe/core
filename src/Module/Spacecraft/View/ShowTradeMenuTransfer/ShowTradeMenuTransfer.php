<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowTradeMenuTransfer;

use Override;
use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenuTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRADEMENU_TRANSFER';
    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spaceCraftLoader
     */
    public function __construct(
        private SpacecraftLoaderInterface $spaceCraftLoader,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private InteractionCheckerInterface $interactionChecker
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $spacecraft = $this->spaceCraftLoader->getByIdAndUser(request::getIntFatal('id'), $userId);

        $mode = request::getStringFatal('mode');
        match ($mode) {
            'from' => $game->showMacro('html/ship/transferfromaccount.twig'),
            default => $game->showMacro('html/ship/transfertoaccount.twig'),
        };

        $tradepost = $this->tradePostRepository->find(request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($spacecraft, $tradepost->getStation())) {
            throw new AccessViolationException();
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountWrapper($tradepost, $userId));
        $game->setTemplateVar('SHIP', $spacecraft);
    }
}
