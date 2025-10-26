<?php

namespace Stu\Module\Control\Router;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Router\Handler\FallbackHandlerInterface;

class FallbackRouter implements FallbackRouterInterface
{
    /** @param array<FallbackHandlerInterface> $handlers */
    public function __construct(
        private array $handlers
    ) {}

    #[\Override]
    public function showFallbackSite(FallbackRouteException $e, GameControllerInterface $game): void
    {
        $className = get_class($e);
        if (!array_key_exists($className, $this->handlers)) {
            throw new FallbackRouteException(
                sprintf('no fallback handler for exception class %s', $className),
                1,
                $e
            );
        }

        $this->handlers[$className]->handle($e, $game);
    }
}
