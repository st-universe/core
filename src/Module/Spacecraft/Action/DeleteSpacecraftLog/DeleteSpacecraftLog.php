<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeleteSpacecraftLog;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowShipCommunication\ShowShipCommunication;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;

final class DeleteSpacecraftLog implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_SPACECRAFT_LOG';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftLogRepositoryInterface $spacecraftLogRepository,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $this->setReturnView($game);

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $spacecraftLog = $this->spacecraftLogRepository->find(
            request::postIntFatal(self::ACTION_IDENTIFIER)
        );

        if ($spacecraftLog === null || $spacecraftLog->getSpacecraftId() !== $spacecraft->getId()) {
            return;
        }

        $spacecraftLog->setDeleted($this->stuTime->time());
        $this->spacecraftLogRepository->save($spacecraftLog);

        $game->getInfo()->addInformation('Logbucheintrag wurde gelöscht');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function setReturnView(GameControllerInterface $game): void
    {
        $game->setView(
            request::postInt('communicationPopup') === 1
                ? ShowShipCommunication::VIEW_IDENTIFIER
                : ShowSpacecraft::VIEW_IDENTIFIER
        );
    }
}
