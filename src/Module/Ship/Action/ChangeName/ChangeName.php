<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ChangeName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private ShipLoaderInterface $shipLoader;

    private Parser $bbCodeParser;

    private ShipRepositoryInterface $shipRepository;

    private ChangeNameRequestInterface $changeNameRequest;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        Parser $bbCodeParser,
        ShipRepositoryInterface $shipRepository,
        ChangeNameRequestInterface $changeNameRequest
    ) {
        $this->shipLoader = $shipLoader;
        $this->bbCodeParser = $bbCodeParser;
        $this->shipRepository = $shipRepository;
        $this->changeNameRequest = $changeNameRequest;
    }

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

    public function performSessionCheck(): bool
    {
        return true;
    }
}
