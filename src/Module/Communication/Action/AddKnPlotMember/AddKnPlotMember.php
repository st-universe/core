<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use Stu\Module\Communication\Lib\PrivateMessageSender;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use User;

final class AddKnPlotMember implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PLOTMEMBER';

    private $addKnPlotMemberRequest;

    private $rpgPlotMemberRepository;

    private $rpgPlotRepository;

    public function __construct(
        AddKnPlotMemberRequestInterface $addKnPlotMemberRequest,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->addKnPlotMemberRequest = $addKnPlotMemberRequest;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var RpgPlotInterface $plot */
        $plot = $this->rpgPlotRepository->find($this->addKnPlotMemberRequest->getPlotId());
        if ($plot === null || $plot->getUserId() != $game->getUser()->getId() || !$plot->isActive()) {
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
        if ($this->rpgPlotMemberRepository->getByPlotAndUser((int) $plot->getId(), (int) $recipient->getId()) !== null) {
            $game->addInformation(_('Dieser Spieler schreibt bereits an diesem Plot'));
            return;
        }

        $member = $this->rpgPlotMemberRepository->prototype()
            ->setUserId((int) $recipient->getId())
            ->setRpgPlot($plot);

        $this->rpgPlotMemberRepository->save($member);

        PrivateMessageSender::sendPM(
            $game->getUser()->getId(),
            $recipient->getId(),
            sprintf(_('Du wurdest dem RPG-Plot \'%s\' als Schreiber hinzugefügt'), $plot->getTitle())
        );

        $game->addInformation(_('Der Spieler wurde hinzugefügt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
