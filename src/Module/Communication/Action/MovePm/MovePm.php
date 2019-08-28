<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\MovePm;

use PM;
use PMCategory;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class MovePm implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_MOVE_PM';

    private $movePmRequest;

    public function __construct(
        MovePmRequestInterface $movePmRequest
    ) {
        $this->movePmRequest = $movePmRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $cat = new PMCategory($this->movePmRequest->getCategoryId());

        if ($cat->isPMOutDir()) {
            return;
        }

        $destination = new PMCategory($this->movePmRequest->getDestinationCategoryId());
        $pm = new PM($this->movePmRequest->getPmId());

        if ($destination->getUserId() != $game->getUser()->getId()) {
            $game->addInformation(_('Dieser Ordner existiert nicht'));
            return;
        }
        if (!$pm->isOwnPM()) {
            $game->addInformation(_('Diese Nachricht existiert nicht'));
            return;
        }
        $pm->setCategoryId($destination->getId());
        $pm->save();

        $game->addInformation(_('Die Nachricht wurde verscheben'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
