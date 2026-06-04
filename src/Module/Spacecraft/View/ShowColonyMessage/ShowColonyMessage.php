<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowColonyMessage;

use request;
use Stu\Component\Colony\ColonyMessageBbCodeParser;
use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ShowColonyMessage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY_MESSAGE';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ColonyMessageBbCodeParser $bbCodeParser
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId(),
            true,
            false
        );

        $starsystemMap = $ship->getStarsystemMap();
        if ($starsystemMap === null) {
            throw new SanityCheckException('ship is not in system');
        }

        $colony = $starsystemMap->getColony();
        if ($colony === null) {
            throw new SanityCheckException('ship is not over colony');
        }

        $colonyMessage = $colony->getChangeable()->getColonyMessage();
        $message = $colonyMessage === null
            ? null
            : $this->bbCodeParser->parse($colonyMessage)->getAsHTML();

        $game->setPageTitle(_('Koloniebotschaft'));
        $game->setMacroInAjaxWindow('html/spacecraft/colonyMessage.twig');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('COLONY_MESSAGE', $message);
        $game->setTemplateVar('HAS_TRANSLATION', $this->hasTranslation($colonyMessage));
        $game->addExecuteJS('initTranslations();', JavascriptExecutionTypeEnum::AFTER_RENDER);
    }

    private function hasTranslation(?string $text): bool
    {
        return $text !== null
            && strpos($text, '[translate]') !== false
            && strpos($text, '[/translate]') !== false;
    }
}
