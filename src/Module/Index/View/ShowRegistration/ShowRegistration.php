<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class ShowRegistration implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REGISTRATION';

    private $factionRepository;

    public function __construct(
        FactionRepositoryInterface $factionRepository
    ) {
        $this->factionRepository = $factionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Registrierung - Star Trek Universe'));
        $game->setTemplateFile('html/registration.xhtml');

        $game->setTemplateVar('REGISTRATION_POSSIBLE', $game->isRegistrationPossible());
        $game->setTemplateVar('POSSIBLE_FACTIONS', $this->factionRepository->getByChooseable(true));
    }
}
