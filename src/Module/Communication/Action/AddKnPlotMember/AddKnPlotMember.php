<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddKnPlotMember implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_PLOTMEMBER';

    private AddKnPlotMemberRequestInterface $addKnPlotMemberRequest;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    private PrivateMessageSenderInterface $privateMessageSender;
    /**
     * @var UserRepositoryInterface
     */
    private UserRepositoryInterface $userRepository;

    public function __construct(
        AddKnPlotMemberRequestInterface $addKnPlotMemberRequest,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->addKnPlotMemberRequest = $addKnPlotMemberRequest;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var RpgPlotInterface $plot */
        $plot = $this->rpgPlotRepository->find($this->addKnPlotMemberRequest->getPlotId());
        if ($plot === null || $plot->getUserId() != $game->getUser()->getId() || !$plot->isActive()) {
            return;
        }

        $recipient = $this->userRepository->find($this->addKnPlotMemberRequest->getRecipientId());
        if ($recipient === null) {
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
            ->setUser($recipient)
            ->setRpgPlot($plot);

        $this->rpgPlotMemberRepository->save($member);

        $this->privateMessageSender->send(
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
