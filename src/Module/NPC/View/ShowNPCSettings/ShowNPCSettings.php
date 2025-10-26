<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowNPCSettings;

use Stu\Component\Game\JavascriptExecutionTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;

final class ShowNPCSettings implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NPC_SETTINGS';

    public function __construct(private FactionRepositoryInterface $factionRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $factionId = $user->getFactionId();
        $faction = $this->factionRepository->find($factionId);

        $hasTranslation = false;
        if ($faction && $faction->getWelcomeMessage()) {
            $text = $faction->getWelcomeMessage();
            $hasTranslation = strpos($text, '[translate]') !== false && strpos($text, '[/translate]') !== false;
        }

        $game->setTemplateFile('html/npc/settings.twig');
        $game->appendNavigationPart('/npc/?SHOW_NPC_SETTINGS=1', _('Settings'));
        $game->setPageTitle(_('Settings'));
        $game->setTemplateVar('FACTION', $faction);
        $game->setTemplateVar('HAS_TRANSLATION', $hasTranslation);

        $game->addExecuteJS("initTranslations();", JavascriptExecutionTypeEnum::AFTER_RENDER);
    }
}
