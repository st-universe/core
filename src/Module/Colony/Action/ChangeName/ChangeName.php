<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Component\Building\BuildingEnum;
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

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        Parser $bbCodeParser,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->bbCodeParser = $bbCodeParser;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU', ColonyEnum::MENU_OPTION]);

        $value = request::postStringFatal('colname');
        $value = tidyString(strip_tags($value));

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
