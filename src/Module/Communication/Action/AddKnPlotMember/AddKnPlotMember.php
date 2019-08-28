<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use PM;
use RPGPlot;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use User;

final class AddKnPlotMember implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PLOTMEMBER';

    private $addKnPlotMemberRequest;

    public function __construct(
        AddKnPlotMemberRequestInterface $addKnPlotMemberRequest
    ) {
        $this->addKnPlotMemberRequest = $addKnPlotMemberRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = new RPGPlot($this->addKnPlotMemberRequest->getPlotId());
        if ($plot->getUserId() != $game->getUser()->getId() || !$plot->isActive()) {
            return;
        }
        $recipient = User::getUserById($this->addKnPlotMemberRequest->getRecipientId());
        if (!$recipient) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($plot->getUserId() == $recipient->getId()) {
            $game->addInformation(_('Du kannst Dich nicht selbst hinzufügen'));
            return;
        }
        if (RPGPlot::checkUserPlot($recipient->getId(), $plot->getId())) {
            $game->addInformation(_('Dieser Spieler schreibt bereits an diesem Plot'));
            return;
        }

        RPGPlot::addPlotMember($recipient->getId(), $plot->getId());
        PM::sendPM(
            $game->getUser()->getId(),
            $recipient->getId(),
            sprintf(_('Du wurdest dem RPG-Plot \'%s\' als Schreiber hinzugefügt'), $plot->getTitleDecoded())
        );

        $game->addInformation(_('Der Spieler wurde hinzugefügt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
