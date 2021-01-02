<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class DoManualColonyTick implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_COLONY_TICK';

    private ColonyTickManagerInterface $colonyTickManager;
    
    private ColonyTickInterface $colonyTick;

    private ColonyRepositoryInterface $colonyRepository;

    public function __construct(
        ColonyTickManagerInterface $colonyTickManager,
        ColonyTickInterface $colonyTick,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyTickManager = $colonyTickManager;
        $this->colonyTick = $colonyTick;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin())
        {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //check if single or all colonies
        if (!request::getVarByMethod(request::postvars(), 'colonytickid'))
        {
            $this->colonyTickManager->work(1);
            $game->addInformation("Der Kolonie-Tick für alle Kolonien wurde durchgeführt!");
        } else 
        {
            $colonyId = request::postInt('colonytickid');
            $colony = $this->colonyRepository->find($colonyId);
    
            $this->colonyTick->work($colony);
            
            $game->addInformation("Der Kolonie-Tick für diese Kolonie wurde durchgeführt!");
        }

        
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
