<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SetPopulationLimit;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class SetPopulationLimit implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_POPULATIONLIMIT';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU' => ColonyMenuEnum::MENU_OPTION]);

        $limit = request::postIntFatal('poplimit');
        if ($limit === $colony->getPopulationLimit() || $limit < 0) {
            return;
        }
        $colony->setPopulationLimit($limit);

        $this->colonyRepository->save($colony);

        if ($limit > 0) {
            $game->addInformationf(_('Das Bevölkerungslimit wurde auf %d gesetzt'), $limit);
        }
        if ($limit == 0) {
            $game->addInformation(_('Das Bevölkerungslimit wurde aufgehoben'));
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
