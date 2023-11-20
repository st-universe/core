<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Sandbox;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Lib\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;

final class ShowColonySandbox implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_SANDBOX';

    private ColonySandboxRepositoryInterface $colonySandboxRepository;

    private PlanetFieldHostProviderInterface $planetFieldHostProvider;

    private ColonyGuiHelperInterface $colonyGuiHelper;

    public function __construct(
        ColonySandboxRepositoryInterface $colonySandboxRepository,
        PlanetFieldHostProviderInterface $planetFieldHostProvider,
        ColonyGuiHelperInterface $colonyGuiHelper
    ) {
        $this->colonySandboxRepository = $colonySandboxRepository;
        $this->planetFieldHostProvider = $planetFieldHostProvider;
        $this->colonyGuiHelper = $colonyGuiHelper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/admin/colonySandbox.twig');
        $game->setPageTitle(_('Kolonie-Sandbox'));

        $game->setTemplateVar('SANDBOXES', $this->colonySandboxRepository->getByUser($game->getUser()));

        $sandbox = $game->getViewContext()['HOST'] ?? null;
        if ($sandbox === null && request::has('id')) {
            $sandbox = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser());
        }
        $game->appendNavigationPart('/admin/?SHOW_COLONY_SANDBOX=1', _('Kolonie-Sandbox'));

        if ($sandbox !== null) {
            $game->appendNavigationPart(
                sprintf(
                    '/admin/?%s=1&id=%d&hosttype=%d',
                    static::VIEW_IDENTIFIER,
                    $sandbox->getId(),
                    PlanetFieldHostTypeEnum::SANDBOX->value
                ),
                $sandbox->getName()
            );

            $this->colonyGuiHelper->registerComponents($sandbox, $game);

            $game->setTemplateVar('HOST', $sandbox);
            $game->setTemplateVar('CURRENT_MENU', ColonyMenuEnum::MENU_INFO);

            $menu = ColonyMenuEnum::getFor($game->getViewContext()['COLONY_MENU'] ?? null);

            $game->setTemplateVar('SELECTED_COLONY_MENU_TEMPLATE', $menu->getTemplate());
        }
    }
}
