<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeName;

use JBBCode\Parser;
use Override;
use request;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ChangeName implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    public function __construct(private ShipLoaderInterface $shipLoader, private Parser $bbCodeParser, private ShipRepositoryInterface $shipRepository, private ChangeNameRequestInterface $changeNameRequest)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $text = $this->changeNameRequest->getName();

        if (!CleanTextUtils::checkBBCode($text)) {
            $game->addInformation(_('Der Name enthält ungültige BB-Code Formatierung'));
            return;
        }

        $value = CleanTextUtils::clearEmojis($text);
        $nameWithoutUnicode = CleanTextUtils::clearUnicode($value);
        if ($value !== $nameWithoutUnicode) {
            $game->addInformation(_('Der Name enthält ungültigen Unicode'));
            return;
        }

        if (mb_strlen($value) > 255) {
            $game->addInformation(_('Der Name ist zu lang (Maximum: 255 Zeichen)'));
            return;
        }

        if (mb_strlen($this->bbCodeParser->parse($value)->getAsText()) < 3) {
            $game->addInformation(_('Der Schiffname ist zu kurz (Minimum 3 Zeichen)'));
            return;
        }

        $ship->setName($value);

        $this->shipRepository->save($ship);

        $game->addInformation("Der Schiffname wurde geändert");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
