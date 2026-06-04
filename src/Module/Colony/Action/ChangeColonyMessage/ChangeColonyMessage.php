<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeColonyMessage;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeColonyMessage implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_COLONY_MESSAGE';

    public function __construct(
        private ColonyLoaderInterface $colonyLoader,
        private ColonyRepositoryInterface $colonyRepository,
        private ChangeColonyMessageRequestInterface $changeColonyMessageRequest
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_OPTION);

        $text = $this->changeColonyMessageRequest->getColonyMessage();
        if ($text === '') {
            $colony->getChangeable()->setColonyMessage(null);
            $this->colonyRepository->save($colony);
            $game->getInfo()->addInformation(_('Die Koloniebotschaft wurde entfernt'));
            return;
        }

        if (mb_strlen($text) < 50) {
            $game->getInfo()->addInformation(_('Die Koloniebotschaft ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }

        $colony->getChangeable()->setColonyMessage($text);
        $this->colonyRepository->save($colony);

        $game->getInfo()->addInformation(_('Die Koloniebotschaft wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
