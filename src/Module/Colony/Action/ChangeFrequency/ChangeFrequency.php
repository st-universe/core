<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeFrequency;

use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ChangeFrequency implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_FREQUENCY';

    public function __construct(private ColonyLoaderInterface $colonyLoader, private ColonyRepositoryInterface $colonyRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $context->getUser()->getId()
        );

        $context->setView(ShowColony::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::COLONY_MENU, ColonyMenuEnum::MENU_INFO);

        $frequency = request::postStringFatal('frequency');

        if (!is_numeric($frequency)) {
            $context->getInfo()->addInformation(_('Nur ganze Zahlen erlaubt'));
            return;
        }

        if (mb_strlen($frequency) > 6) {
            $context->getInfo()->addInformation(_('Unerlaubte Frequenz (Maximum: 6 Zeichen)'));
            return;
        }
        $colony->getChangeable()->setShieldFrequency((int)$frequency);
        $this->colonyRepository->save($colony);

        $context->getInfo()->addInformation(_('Die Schildfrequenz wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
