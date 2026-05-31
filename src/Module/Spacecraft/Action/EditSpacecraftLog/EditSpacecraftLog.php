<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\EditSpacecraftLog;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowShipCommunication\ShowShipCommunication;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;

final class EditSpacecraftLog implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_SPACECRAFT_LOG';

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

        $spacecraftLog = $this->spacecraftLogRepository->find(request::postIntFatal('logid'));

        if (
            $spacecraftLog === null
            || $spacecraftLog->isDeleted()
            || $spacecraftLog->getSpacecraftId() !== $spacecraft->getId()
        ) {
            return;
        }

        $spacecraftLog->setText(request::postStringFatal('log'));
        $spacecraftLog->setEdited($this->stuTime->time());
        $this->spacecraftLogRepository->save($spacecraftLog);

        $game->getInfo()->addInformation('Logbucheintrag wurde bearbeitet');
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
