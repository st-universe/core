<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowComponent;

use Override;
use request;
use RuntimeException;
use Stu\Component\Game\ModuleEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Lib\Component\EntityWithComponentsInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Game\Component\GameComponentEnum;

final class ShowComponent implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMPONENT';

    public function __construct(
        private ComponentRegistrationInterface $componentRegistration,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $exploded = explode('_', request::getStringFatal('component'), 2);
        $moduleView = ModuleEnum::tryFrom(strtolower($exploded[0]));
        $componentEnum = $this->getComponentEnum($moduleView, $exploded);

        $this->componentRegistration->registerComponent($componentEnum, $this->getEntity($moduleView, $game));
        $game->showMacro($componentEnum->getTemplate());
    }

    /** @param array<string> $exploded */
    private function getComponentEnum(?ModuleEnum $moduleView, array $exploded): ComponentEnumInterface
    {
        if (
            $moduleView === null
            || !array_key_exists(1, $exploded)
        ) {
            return GameComponentEnum::OUTDATED;
        }

        return $moduleView->getComponentEnum($exploded[1]);
    }

    private function getEntity(?ModuleEnum $moduleView, GameControllerInterface $game): ?EntityWithComponentsInterface
    {
        if ($moduleView === null) {
            return null;
        }

        $entityId = request::getInt('id');
        if (!$entityId) {
            return null;
        }

        return match ($moduleView) {
            ModuleEnum::COLONY => $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false),
            default => throw new RuntimeException(sprintf('module view %s is not supported', $moduleView->value))
        };
    }
}
