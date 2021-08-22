<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

use Stu\Exception\AccessViolation;
use Stu\Lib\EmojiRemover;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class RenameBuildplan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILDPLAN_CHANGE_NAME';

    private RenameBuildplanRequestInterface $renameBuildplanRequest;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    public function __construct(
        RenameBuildplanRequestInterface $renameBuildplanRequest,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->renameBuildplanRequest = $renameBuildplanRequest;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $newName = EmojiRemover::clearEmojis($this->renameBuildplanRequest->getNewName());
        if (mb_strlen($newName) === 0) {
            return;
        }

        if (mb_strlen($newName) > 255) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        $plan = $this->shipBuildplanRepository->find($this->renameBuildplanRequest->getId());

        if ($plan === null || $plan->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $plan->setName($newName);

        $this->shipBuildplanRepository->save($plan);

        $game->addInformation(_('Der Name des Bauplans wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
