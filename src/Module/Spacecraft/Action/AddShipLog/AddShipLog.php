<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AddShipLog;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowShipCommunication\ShowShipCommunication;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftLogRepositoryInterface;

final class AddShipLog implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_SHIP_LOG';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftLogRepositoryInterface $spacecraftLogRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $this->setReturnView($context);

        $user = $context->getUser();
        $userId = $user->getId();

        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $text = request::postStringFatal('log');

        $spacecraftLog = $this->spacecraftLogRepository->prototype();
        $spacecraftLog->setSpacecraft($spacecraft);
        $spacecraftLog->setText($text);
        $spacecraftLog->setDate(time());

        $this->spacecraftLogRepository->save($spacecraftLog);

        $context->getInfo()->addInformation('Logbucheintrag wurde hinzugefügt');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function setReturnView(ActionControllerContext $context): void
    {
        $context->setView(
            request::postInt('communicationPopup') === 1
                ? ShowShipCommunication::VIEW_IDENTIFIER
                : ShowSpacecraft::VIEW_IDENTIFIER
        );
    }
}
