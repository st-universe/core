<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\AllowImmigration;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class AllowImmigration implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ALLOW_IMMIGRATION';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $context->getUser()->getId()
        );

        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_OPTION);

        $colony->getChangeable()->setImmigrationState(true);

        $this->colonyRepository->save($colony);

        $context->getInfo()->addInformation(_('Die Einwanderung wurde erlaubt'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
