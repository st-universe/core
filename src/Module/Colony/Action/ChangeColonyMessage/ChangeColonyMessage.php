<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeColonyMessage;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
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
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $context->getUser()->getId()
        );

        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_OPTION);

        $text = $this->changeColonyMessageRequest->getColonyMessage();
        if ($text === '') {
            $colony->getChangeable()->setColonyMessage(null);
            $this->colonyRepository->save($colony);
            $context->getInfo()->addInformation(_('Die Koloniebotschaft wurde entfernt'));
            return;
        }

        if (mb_strlen($text) < 50) {
            $context->getInfo()->addInformation(_('Die Koloniebotschaft ist zu kurz (mindestens 50 Zeichen)'));
            return;
        }

        $colony->getChangeable()->setColonyMessage($text);
        $this->colonyRepository->save($colony);

        $context->getInfo()->addInformation(_('Die Koloniebotschaft wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
