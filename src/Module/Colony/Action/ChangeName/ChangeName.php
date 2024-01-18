<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private ColonyLoaderInterface $colonyLoader;

    private Parser $bbCodeParser;

    private ColonyRepositoryInterface $colonyRepository;

    private ChangeNameRequestInterface $changeNameRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        Parser $bbCodeParser,
        ColonyRepositoryInterface $colonyRepository,
        ChangeNameRequestInterface $changeNameRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->bbCodeParser = $bbCodeParser;
        $this->colonyRepository = $colonyRepository;
        $this->changeNameRequest = $changeNameRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => ColonyMenuEnum::MENU_OPTION]);

        $text = $this->changeNameRequest->getName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $value = CleanTextUtils::clearEmojis($text);
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($value);
        if ($value !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($value) > 255) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if (mb_strlen($this->bbCodeParser->parse($value)->getAsText()) < 3) {
            $game->addInformation(_('Der Name ist zu kurz (Minium: 3 Zeichen)'));
            return;
        }
        $colony->setName($value);
        $this->colonyRepository->save($colony);

        $game->addInformation(_('Der Koloniename wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
