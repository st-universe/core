<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\CollectBorgNanites;

use Override;
use request;
use Stu\Component\Borg\BorgSphereRaidService;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class CollectBorgNanites implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_COLLECT_BORG_NANITES';

    public function __construct(private ShipLoaderInterface $ships, private BorgSphereRaidService $raids) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $wrappers = $this->ships->getWrappersBySourceAndUserAndTarget(request::indInt('id'), $context->getUser()->getId(), request::indInt('target'));
        $target = $wrappers->getTarget()?->get();
        if ($target !== null) {
            $this->raids->collect($wrappers->getSource(), $target, $context->getInfo());
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
