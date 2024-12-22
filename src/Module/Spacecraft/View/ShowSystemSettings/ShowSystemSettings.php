<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowSystemSettings implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SYSTEM_SETTINGS_AJAX';

    /** 
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader 
     * @param array<int, SystemSettingsProviderInterface> $systemSettingsProvider
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private array $systemSettingsProvider
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );

        $systemType = SpacecraftSystemTypeEnum::getByName(request::getStringFatal('system'));

        $game->setTemplateVar('WRAPPER', $wrapper);
        $game->setPageTitle($systemType->getDescription());

        $this->systemSettingsProvider[$systemType->value]->setTemplateVariables(
            $systemType,
            $wrapper,
            $game
        );
    }
}
