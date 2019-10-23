<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class ShowRegistration implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REGISTRATION';

    private $showRegistrationRequest;

    private $factionRepository;

    public function __construct(
        ShowRegistrationRequestInterface $showRegistrationRequest,
        FactionRepositoryInterface $factionRepository
    ) {
        $this->showRegistrationRequest = $showRegistrationRequest;
        $this->factionRepository = $factionRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Registrierung - Star Trek Universe'));
        $game->setTemplateFile('html/registration.xhtml');

        $game->setTemplateVar('REGISTRATION_POSSIBLE', $game->isRegistrationPossible());
        $game->setTemplateVar('POSSIBLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('TOKEN', $this->showRegistrationRequest->getToken());
    }
}
