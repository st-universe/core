<?php

declare(strict_types=1);

namespace Stu\Module\Control\Router\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Game\RedirectionException;
use Stu\Component\Logging\GameRequest\GameRequestSaverInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Router\FallbackRouteException;

class RedirectionFallbackHandler implements FallbackHandlerInterface
{
    public function __construct(
        private readonly GameRequestSaverInterface $gameRequestSaver,
        private readonly EntityManagerInterface $entityManager
    ) {}

    /** @param RedirectionException $e */
    #[\Override]
    public function handle(FallbackRouteException $e, ViewControllerContext $context): void
    {
        $this->gameRequestSaver->save($context->getGame()->getGameRequest());
        $this->entityManager->flush();
        $this->entityManager->commit();
        header('Location: ' . $e->getHref());
        exit();
    }
}
