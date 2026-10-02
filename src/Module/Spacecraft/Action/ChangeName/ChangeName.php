<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class ChangeName implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private Parser $bbCodeParser,
        private ChangeNameRequestInterface $changeNameRequest
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $context->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $text = $this->changeNameRequest->getName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $value = CleanTextUtils::clearEmojis($text);
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($value);
        if ($value !== $nameWithoutUnicode) {
            $context->getInfo()->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($value) > 255) {
            $context->getInfo()->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if (mb_strlen($this->bbCodeParser->parse($value)->getAsText()) < 3) {
            $context->getInfo()->addInformation(_('Der Schiffname ist zu kurz (Minimum 3 Zeichen)'));
            return;
        }

        $ship->setName($value);

        $context->getInfo()->addInformation("Der Schiffname wurde geändert");
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
