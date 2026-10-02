<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\SetPopulationLimit;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class SetPopulationLimit implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_POPULATIONLIMIT';

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

        $limit = request::postIntFatal('poplimit');
        if ($limit === $colony->getChangeable()->getPopulationLimit() || $limit < 0) {
            return;
        }
        $colony->getChangeable()->setPopulationLimit($limit);

        $this->colonyRepository->save($colony);

        if ($limit > 0) {
            $context->getInfo()->addInformationf(_('Das Bevölkerungslimit wurde auf %d gesetzt'), $limit);
        }
        if ($limit === 0) {
            $context->getInfo()->addInformation(_('Das Bevölkerungslimit wurde aufgehoben'));
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
