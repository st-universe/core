<?php

declare(strict_types=1);

namespace Stu\Extension;

use Psr\Container\ContainerInterface;
use RuntimeException;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Game\Lib\View\Provider\ViewComponentProviderInterface;

final class ExtensionHooks
{
    public function __construct(private ExtensionRegistry $registry, private ContainerInterface $container) {}

    public function view(string $hook, ViewControllerContext $game): void
    {
        foreach ($this->registry->handlers($hook) as $class) {
            $handler = $this->container->get($class);
            if (!$handler instanceof ViewComponentProviderInterface) {
                throw new RuntimeException('Invalid extension view handler');
            }
            $handler->setTemplateVariables($game);
        }
    }

    public function interaction(EntityWithInteractionCheckInterface $source, EntityWithInteractionCheckInterface $target): ?string
    {
        foreach ($this->registry->handlers('interaction') as $class) {
            $handler = $this->container->get($class);
            if (!$handler instanceof InteractionGuardInterface) {
                throw new RuntimeException('Invalid extension interaction guard');
            }
            $reason = $handler->getRefusalReason($source, $target);
            if ($reason !== null) {
                return $reason;
            }
        }
        return null;
    }
}
