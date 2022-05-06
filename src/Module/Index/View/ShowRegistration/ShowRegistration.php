<?php

declare(strict_types=1);

namespace Stu\Module\Index\View\ShowRegistration;

use Noodlehaus\ConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class ShowRegistration implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_REGISTRATION';

    private ShowRegistrationRequestInterface $showRegistrationRequest;

    private FactionRepositoryInterface $factionRepository;

    private ConfigInterface $config;

    public function __construct(
        ShowRegistrationRequestInterface $showRegistrationRequest,
        FactionRepositoryInterface $factionRepository,
        ConfigInterface $config
    ) {
        $this->showRegistrationRequest = $showRegistrationRequest;
        $this->factionRepository = $factionRepository;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Registrierung - Star Trek Universe'));
        $game->setTemplateFile('html/registration.xhtml');

        $game->setTemplateVar('REGISTRATION_POSSIBLE', $this->config->get('game.registration.enabled'));
        $game->setTemplateVar('IS_SMS_REGISTRATION', $this->config->get('game.registration.sms_code_verification.enabled'));
        $game->setTemplateVar('POSSIBLE_FACTIONS', $this->factionRepository->getByChooseable(true));
        $game->setTemplateVar('TOKEN', $this->showRegistrationRequest->getToken());
        $game->setTemplateVar('WIKI', $this->config->get('wiki.base_url'));
    }
}
