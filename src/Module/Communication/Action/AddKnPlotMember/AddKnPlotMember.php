<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddKnPlotMember implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_PLOTMEMBER';

    public function __construct(private AddKnPlotMemberRequestInterface $addKnPlotMemberRequest, private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository, private RpgPlotRepositoryInterface $rpgPlotRepository, private PrivateMessageSenderInterface $privateMessageSender, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var RpgPlot $plot */
        $plot = $this->rpgPlotRepository->find($this->addKnPlotMemberRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $game->getUser()->getId() || !$plot->isActive()) {
            return;
        }

        $recipient = $this->userRepository->find($this->addKnPlotMemberRequest->getRecipientId());
        if ($recipient === null) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($plot->getUserId() === $recipient->getId()) {
            $game->addInformation(_('Du kannst Dich nicht selbst hinzufügen'));
            return;
        }
        if ($this->rpgPlotMemberRepository->getByPlotAndUser($plot->getId(), $recipient->getId()) !== null) {
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
