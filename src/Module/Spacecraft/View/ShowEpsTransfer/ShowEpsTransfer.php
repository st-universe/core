<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowEpsTransfer;

use request;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowEpsTransfer implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ETRANSFER';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId,
            true,
            false
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $game->setPageTitle("Energietransfer");
        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
            ])
            ->check($game->getInfo())) {
            return;
        }

        $game->setMacroInAjaxWindow('html/ship/showshipetransfer.twig');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('WRAPPER', $wrapper);
    }
}
