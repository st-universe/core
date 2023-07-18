<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RenameBuildplan;

use Stu\Exception\AccessViolation;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\View\ShowModuleScreenBuildplan\ShowModuleScreenBuildplan;
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
        $userId = $game->getUser()->getId();
        $game->setView(ShowModuleScreenBuildplan::VIEW_IDENTIFIER);

        $newName = CleanTextUtils::clearEmojis($this->renameBuildplanRequest->getNewName());
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 255) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if ($this->shipBuildplanRepository->findByUserAndName($userId, $newName) !== null) {
            $game->addInformation(_('Ein Bauplan mit diesem Namen existiert bereits'));
            return;
        }

        $plan = $this->shipBuildplanRepository->find($this->renameBuildplanRequest->getId());
        if ($plan === null || $plan->getUserId() !== $userId) {
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
