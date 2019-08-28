<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeDescription;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ChangeDescription implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_DESCRIPTION';

    private $changeDescriptionRequest;

    public function __construct(
        ChangeDescriptionRequestInterface $changeDescriptionRequest
    ) {
        $this->changeDescriptionRequest = $changeDescriptionRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = $this->changeDescriptionRequest->getDescription();
        $value = strip_tags(tidyString($value));

        $user = $game->getUser();

        $user->setDescription($value);
        $user->save();

        $game->addInformation(_('Deine Beschreibung wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
