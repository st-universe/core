<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Abandon;

use AccessViolation;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class Abandon implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    private $abandonRequest;

    private $colonyRepository;

    private $colonyResetter;

    public function __construct(
        AbandonRequestInterface $abandonRequest,
        ColonyRepositoryInterface $colonyRepository,
        ColonyResetterInterface $colonyResetter
    ) {
        $this->abandonRequest = $abandonRequest;
        $this->colonyRepository = $colonyRepository;
        $this->colonyResetter = $colonyResetter;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyRepository->find($this->abandonRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $game->getUser()->getId()) {
            throw new AccessViolation();
        }

        $this->colonyResetter->reset($colony);

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
