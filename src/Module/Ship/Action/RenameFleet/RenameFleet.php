<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameFleet;

use Stu\Exception\AccessViolationException;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class RenameFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_CHANGE_NAME';

    public function __construct(private RenameFleetRequestInterface $renameFleetRequest, private FleetRepositoryInterface $fleetRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $text = $this->renameFleetRequest->getNewName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $newName = CleanTextUtils::clearEmojis($text);
        if (mb_strlen($newName) === 0) {
            return;
        }

        $nameWithoutUnicode = CleanTextUtils::clearUnicode($newName);
        if ($newName !== $nameWithoutUnicode) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($newName) > 200) {
            $context->getInfo()->addInformation(_('Der Name ist zu lang (Maximum: 200 Zeichen)'));
            return;
        }

        $fleet = $this->fleetRepository->find($this->renameFleetRequest->getFleetId());

        if ($fleet === null || $fleet->getUserId() !== $context->getUser()->getId()) {
            throw new AccessViolationException();
        }

        $fleet->setName($newName);

        $context->getInfo()->addInformation(_('Der Name der Flotte wurde geändert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
