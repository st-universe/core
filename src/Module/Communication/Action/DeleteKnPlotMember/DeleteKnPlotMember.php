<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPlotMember;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\RpgPlotMemberRepositoryInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;

final class DeleteKnPlotMember implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_PLOTMEMBER';

    private DeleteKnPlotMemberRequestInterface $deleteKnPlotMemberRequest;

    private RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository;

    private RpgPlotRepositoryInterface $rpgPlotRepository;

    public function __construct(
        DeleteKnPlotMemberRequestInterface $deleteKnPlotMemberRequest,
        RpgPlotMemberRepositoryInterface $rpgPlotMemberRepository,
        RpgPlotRepositoryInterface $rpgPlotRepository
    ) {
        $this->deleteKnPlotMemberRequest = $deleteKnPlotMemberRequest;
        $this->rpgPlotMemberRepository = $rpgPlotMemberRepository;
        $this->rpgPlotRepository = $rpgPlotRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $plot = $this->rpgPlotRepository->find($this->deleteKnPlotMemberRequest->getPlotId());
        if ($plot === null || $plot->getUserId() !== $game->getUser()->getId() || !$plot->isActive()) {
            return;
        }

        $recipientId = $this->deleteKnPlotMemberRequest->getRecipientId();

        if ($plot->getUserId() === $recipientId) {
            $game->addInformation(_('Du kannst Dich nicht selbst entfernen'));
            return;
        }
        $item = $this->rpgPlotMemberRepository->getByPlotAndUser((int) $plot->getId(), $recipientId);
        if ($item !== null) {
            $this->rpgPlotMemberRepository->delete($item);

            $game->addInformation(_('Der Spieler wurde entfernt'));
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
