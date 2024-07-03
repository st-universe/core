<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile;

use Stu\Module\Control\GameController;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurface;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurfaceRequest;
use Stu\Module\Database\View\ShowColonySurface\ShowColonySurfaceRequestInterface;
use Stu\Module\Game\View\Overview\Overview;
use Stu\Module\PlayerProfile\Action\ChangeCharacter\ChangeCharacter;
use Stu\Module\PlayerProfile\Action\ChangeCharacter\ChangeCharacterRequest;
use Stu\Module\PlayerProfile\Action\ChangeCharacter\ChangeCharacterRequestInterface;
use Stu\Module\PlayerProfile\Action\CreateCharacter\CreateCharacter;
use Stu\Module\PlayerProfile\Action\CreateCharacter\CreateCharacterRequest;
use Stu\Module\PlayerProfile\Action\CreateCharacter\CreateCharacterRequestInterface;
use Stu\Module\PlayerProfile\Action\DeleteCharacter\DeleteCharacter;
use Stu\Module\PlayerProfile\Action\DeleteCharacter\DeleteCharacterRequest;
use Stu\Module\PlayerProfile\Action\DeleteCharacter\DeleteCharacterRequestInterface;


use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistration;
use Stu\Module\PlayerProfile\Lib\ProfileVisitorRegistrationInterface;

use function DI\autowire;

return [
    ProfileVisitorRegistrationInterface::class => autowire(ProfileVisitorRegistration::class),
    CreateCharacterRequestInterface::class => autowire(CreateCharacterRequest::class),
    ChangeCharacterRequestInterface::class => autowire(ChangeCharacterRequest::class),
    DeleteCharacterRequestInterface::class => autowire(DeleteCharacterRequest::class),
    ShowColonySurfaceRequestInterface::class => autowire(ShowColonySurfaceRequest::class),
    'PLAYER_PROFILE_ACTIONS' => [
        CreateCharacter::ACTION_IDENTIFIER => autowire(CreateCharacter::class),
        ChangeCharacter::ACTION_IDENTIFIER => autowire(ChangeCharacter::class),
        DeleteCharacter::ACTION_IDENTIFIER => autowire(DeleteCharacter::class),
    ],
    'PLAYER_PROFILE_VIEWS' => [
        GameController::DEFAULT_VIEW => autowire(Overview::class),
        ShowColonySurface::VIEW_IDENTIFIER => autowire(ShowColonySurface::class),
    ],
];
