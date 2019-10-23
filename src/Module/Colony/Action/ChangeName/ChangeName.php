<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Component\Colony\ColonyEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private $colonyLoader;

    private $bbCodeParser;

    private $colonyRepository;

    private $changeNameRequest;

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
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU', ColonyEnum::MENU_OPTION]);

        $value = $this->changeNameRequest->getName();

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
