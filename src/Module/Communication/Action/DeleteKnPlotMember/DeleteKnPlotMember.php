<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPlotMember;

use RPGPlot;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class DeleteKnPlotMember implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_PLOTMEMBER';

    private $deleteKnPlotMemberRequest;

    public function __construct(
        DeleteKnPlotMemberRequestInterface $deleteKnPlotMemberRequest
    ) {
        $this->deleteKnPlotMemberRequest = $deleteKnPlotMemberRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->deleteKnPlotMemberRequest->getPlotId());
        if ($plot->getUserId() != $game->getUser()->getId() || !$plot->isActive()) {
            return;
        }

        $recipientId = $this->deleteKnPlotMemberRequest->getRecipientId();

        if ($plot->getUserId() == $recipientId) {
            $game->addInformation(_('Du kannst Dich nicht selbst entfernen'));
            return;
        }
        if (!RPGPlot::checkUserPlot($recipientId, $plot->getId())) {
            return;
        }
        RPGPlot::delPlotMember($recipientId, $plot->getId());

        $game->addInformation(_('Der Spieler wurde entfernt'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
