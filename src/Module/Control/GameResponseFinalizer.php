<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use request;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\GameRequest;

final class GameResponseFinalizer implements GameResponseFinalizerInterface
{
    public function __construct(
        private readonly TwigPageInterface $twigPage,
        private readonly ComponentSetupInterface $componentSetup,
        private readonly GameTwigRendererInterface $gameTwigRenderer
    ) {}

    #[\Override]
    public function finalize(GameControllerInterface $game, GameRequest $gameRequest): string
    {
        if (!$this->twigPage->isTemplateSet()) {
            StuLogger::logf('NO TEMPLATE FILE SPECIFIED, Method: %s', request::isPost() ? 'POST' : 'GET');
            StuLogger::log(print_r(request::isPost() ? request::postvars() : request::getvars(), true));
        }

        $this->componentSetup->setup($game);

        $user = $game->hasUser() ? $game->getUser() : null;
        $startTime = hrtime(true);
        $renderResult = $this->gameTwigRenderer->render($game, $user);
        $renderMs = hrtime(true) - $startTime;

        $gameRequest->setRenderMs((int)ceil($renderMs / 1_000_000));

        return $renderResult;
    }
}
