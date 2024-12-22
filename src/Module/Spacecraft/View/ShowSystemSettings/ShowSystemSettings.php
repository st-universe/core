<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSystemSettings;

use Override;
use request;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Config\Init;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowSystemSettings implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SYSTEM_SETTINGS_AJAX';

    /** 
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader 
     */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader
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

        $this->getSettingsProvider($systemType)->setTemplateVariables(
            $systemType,
            $wrapper,
            $game
        );
    }

    private function getSettingsProvider(SpacecraftSystemTypeEnum $type): SystemSettingsProviderInterface
    {
        $settingsProvider = Init::getContainer()->getDefinedImplementationsOf(SystemSettingsProviderInterface::class)->get($type->value);
        if ($settingsProvider === null) {
            throw new RuntimeException(sprintf('transfer strategy with typeValue %d does not exist', $type->value));
        }

        return $settingsProvider;
    }
}
