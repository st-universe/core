<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeFrequency;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeFrequency implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_FREQUENCY';

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
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $game->setViewContext(ViewContextTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_INFO);

        $frequency = request::postStringFatal('frequency');

        if (!is_numeric($frequency)) {
            $game->addInformation(_('Nur ganze Zahlen erlaubt'));
            return;
        }

        if (mb_strlen($frequency) > 6) {
            $game->addInformation(_('Unerlaubte Frequenz (Maximum: 6 Zeichen)'));
            return;
        }
        $colony->setShieldFrequency((int)$frequency);
        $this->colonyRepository->save($colony);

        $game->addInformation(_('Die Schildfrequenz wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
