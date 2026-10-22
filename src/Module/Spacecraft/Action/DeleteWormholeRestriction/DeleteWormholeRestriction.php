<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeleteWormholeRestriction;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Spacecraft\View\ShowWormholeRestrictions\ShowWormholeRestrictions;
use Stu\Orm\Repository\WormholeEntryRepositoryInterface;
use Stu\Orm\Repository\WormholeRestrictionRepositoryInterface;

final class DeleteWormholeRestriction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_WORMHOLE_RESTRICTION';

    public function __construct(
        private WormholeRestrictionRepositoryInterface $wormholeRestrictionRepository,
        private WormholeEntryRepositoryInterface $wormholeEntryRepository,
        private GameUserRoleCheckerInterface $gameUserRoleChecker
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!$this->gameUserRoleChecker->isAdmin()) {
            throw new AccessViolationException();
        }

        $entryId = request::getIntFatal('entryId');
        $restrictionId = request::getIntFatal('restrictionId');

        $context->setView(ShowWormholeRestrictions::VIEW_IDENTIFIER);

        $restriction = $this->wormholeRestrictionRepository->find($restrictionId);
        $wormholeEntry = $this->wormholeEntryRepository->find($entryId);

        if (
            $restriction === null
            || $wormholeEntry === null
            || $restriction->getWormholeEntry()->getId() !== $wormholeEntry->getId()
        ) {
            return;
        }

        $this->wormholeRestrictionRepository->delete($restriction);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
