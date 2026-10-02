<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeTorpedoType;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ChangeTorpedoType implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_TORPS';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyRepositoryInterface $colonyRepository, private ChangeTorpedoTypeRequestInterface $changeTorpedoTypeRequest, private TorpedoTypeRepositoryInterface $torpedoTypeRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $context->getUser()->getId()
        );

        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_INFO);

        $torpedoId = $this->changeTorpedoTypeRequest->getTorpedoId();

        if ($torpedoId !== 0) {
            $availableTorpedos = $this->torpedoTypeRepository->getForUser($context->getUser()->getId());
            if (!array_key_exists($torpedoId, $availableTorpedos)) {
                $context->getInfo()->addInformation(_('Unerlaubter Torpedo-Typ'));
                return;
            }

            $colony->getChangeable()->setTorpedo($availableTorpedos[$torpedoId]);
        } else {
            $colony->getChangeable()->setTorpedo(null);
        }
        $this->colonyRepository->save($colony);

        $context->getInfo()->addInformation(_('Die Torpedo-Sorte wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
