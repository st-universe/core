<?php

declare(strict_types=1);

namespace Stu\Module\Game\View\ShowComponent;

use Override;
use request;
use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Lib\Component\ComponentRegistrationInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;

final class ShowComponent implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COMPONENT';

    public function __construct(
        private ComponentRegistrationInterface $componentRegistration,
        private ColonyLoaderInterface $colonyLoader
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $exploded = explode('_', request::getStringFatal('id'), 2);
        $moduleView = ModuleViewEnum::from(strtolower($exploded[0]));
        $componentEnum = $moduleView->getComponentEnum($exploded[1]);

        $this->componentRegistration->registerComponent($componentEnum, $this->getEntity($moduleView, $game->getUser()));
        $game->showMacro($componentEnum->getTemplate());
    }

    private function getEntity(ModuleViewEnum $moduleView, UserInterface $user): ?object
    {
        $entityId = request::getInt('entityid');
        if (!$entityId) {
            return null;
        }

        return match ($moduleView) {
            ModuleViewEnum::COLONY => $this->colonyLoader->loadWithOwnerValidation($entityId, $user->getId(), false),
            default => throw new RuntimeException(sprintf('module view %s is not supported', $moduleView->value))
        };
    }
}
