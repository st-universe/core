<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AddShipLog;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;

final class AddShipLog implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_SHIP_LOG';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftLogRepositoryInterface $spacecraftLogRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $userId = $user->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $text = request::postStringFatal('log');

        $spacecraftLog = $this->spacecraftLogRepository->prototype();
        $spacecraftLog->setSpacecraft($spacecraft);
        $spacecraftLog->setText($text);
        $spacecraftLog->setDate(time());

        $this->spacecraftLogRepository->save($spacecraftLog);

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $game->getInfo()->addInformation('Logbucheintrag wurde hinzugefügt');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
