<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\RenameCrew;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowRenameCrew\ShowRenameCrew;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class RenameCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RENAME_CREW';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CrewRepositoryInterface $crewRepository,
        private RenameCrewRequestInterface $renameCrewRequest
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setView(ShowRenameCrew::VIEW_IDENTIFIER);
        $crew_id = request::getIntFatal('crewid');

        $crew = $this->crewRepository->find($crew_id);

        if ($crew === null || $crew->getUser()->getId() !== $userId) {
            throw new AccessViolation();
        }

        $name = $this->renameCrewRequest->getName($crew->getId());
        if (mb_strlen(trim($name)) > 0) {
            $crew->setName($name);

            $this->crewRepository->save($crew);
        }
        $game->setTemplateVar('CREW', $crew);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
