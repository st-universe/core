<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowEpsBar;

use Override;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowEpsBar implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_EPSBAR_AJAX';

    public function __construct(private PlanetFieldHostProviderInterface $planetFieldHostProvider, private ColonyGuiHelperInterface $colonyGuiHelper) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $host = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);

        $this->colonyGuiHelper->registerComponents($host, $game, [ColonyComponentEnum::EPS_BAR]);

        $game->showMacro('html/colony/component/colonyEps.twig');
    }
}
