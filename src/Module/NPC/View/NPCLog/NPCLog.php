<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\NPCLog;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class NPCLog implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NPC_LOG';

    public function __construct(private NPCLogRepositoryInterface $npclogRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/npc/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('NPC Log')
        );

        $logs = $this->npclogRepository->findBy([], ['id' => 'DESC'], 100);

        $game->setTemplateFile('html/npc/npclog.twig');
        $game->setPageTitle(_('NPC Log'));
        $game->setTemplateVar(
            'LIST',
            $logs
        );
    }
}