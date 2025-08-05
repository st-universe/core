<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use JBBCode\Parser;
use Override;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeName implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private Parser $bbCodeParser, private ColonyRepositoryInterface $colonyRepository, private ChangeNameRequestInterface $changeNameRequest) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_OPTION);

        $text = $this->changeNameRequest->getName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->getInfo()->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $value = CleanTextUtils::clearEmojis($text);
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($value);
        if ($value !== $nameWithoutUnicode) {
            $game->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($value) > 255) {
            $game->getInfo()->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if (mb_strlen($this->bbCodeParser->parse($value)->getAsText()) < 3) {
            $game->getInfo()->addInformation(_('Der Name ist zu kurz (Minium: 3 Zeichen)'));
            return;
        }
        $colony->setName($value);
        $this->colonyRepository->save($colony);

        $game->getInfo()->addInformation(_('Der Koloniename wurde geändert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
