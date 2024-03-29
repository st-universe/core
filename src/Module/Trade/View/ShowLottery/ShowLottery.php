<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowLottery;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\LotteryFacadeInterface;
use Stu\Orm\Repository\LotteryTicketRepositoryInterface;

final class ShowLottery implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_LOTTERY';

    private LotteryFacadeInterface $lotteryFacade;

    private LotteryTicketRepositoryInterface $lotteryTicketRepository;

    public function __construct(
        LotteryFacadeInterface $lotteryFacade,
        LotteryTicketRepositoryInterface $lotteryTicketRepository
    ) {
        $this->lotteryFacade = $lotteryFacade;
        $this->lotteryTicketRepository = $lotteryTicketRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', static::VIEW_IDENTIFIER),
            _('Lotterie')
        );
        $game->setPageTitle(_('/ Handel / Nagus Lotterie'));
        $game->setTemplateFile('html/lottery.xhtml');

        $ticketCount = $this->lotteryFacade->getTicketAmount(false);
        $ownCount = $this->lotteryFacade->getTicketAmountByUser($game->getUser()->getId(), false);

        $game->setTemplateVar('TICKETCOUNT', $ticketCount);
        $game->setTemplateVar('OWNCOUNT', $ownCount);
        $game->setTemplateVar('WINCHANCE', $ticketCount === 0 || $ownCount === 0 ? '-' : (int)ceil($ownCount / $ticketCount * 100));

        $game->setTemplateVar('HISTORY', $this->lotteryTicketRepository->getLotteryHistory());
    }
}
