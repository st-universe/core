<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\NPCLog;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\NPCLogRepositoryInterface;

final class NPCLog implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NPC_LOG';

    private const int LIMIT = 100;

    private const string TYPE_NORMAL = 'normal';

    private const string TYPE_FACTION = 'faction';

    public function __construct(private NPCLogRepositoryInterface $npclogRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                '/npc/?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('NPC Log')
        );

        $count = request::indInt('nlogcount');
        if ($count === 0) {
            $count = self::LIMIT;
        }

        $search = trim(request::indString('nlogsearch') ?: '');
        $sourceUserId = request::indInt('nloguserid');
        $includeAdminView = $game->isAdmin() && request::indInt('nlogadminview') === 1;
        $type = request::indString('nlogtype') ?: self::TYPE_NORMAL;
        $factionId = $game->getUser()->getFactionId();

        $normalLogCount = $this->npclogRepository->getAmountByFaction(null, $includeAdminView);
        $factionLogCount = $this->npclogRepository->getAmountByFaction($factionId, $includeAdminView);

        if ($type !== self::TYPE_FACTION || $factionLogCount === 0) {
            $type = self::TYPE_NORMAL;
        }

        $availableCount = $type === self::TYPE_FACTION ? $factionLogCount : $normalLogCount;
        if ($availableCount > 0 && $count > $availableCount) {
            $count = $availableCount;
        }

        $logTypes = [
            self::TYPE_NORMAL => [
                'value' => self::TYPE_NORMAL,
                'name' => _('NPC Log'),
                'count' => $normalLogCount,
                'class' => $type === self::TYPE_NORMAL ? 'selected' : ''
            ]
        ];

        if ($factionLogCount > 0) {
            $logTypes[self::TYPE_FACTION] = [
                'value' => self::TYPE_FACTION,
                'name' => _('Fraktions-Log'),
                'count' => $factionLogCount,
                'class' => $type === self::TYPE_FACTION ? 'selected' : ''
            ];
        }

        $logs = $this->npclogRepository->getByFactionAndSearch(
            $type === self::TYPE_FACTION ? $factionId : null,
            $count,
            $search,
            $sourceUserId,
            $includeAdminView
        );

        $game->setTemplateFile('html/npc/npclog.twig');
        $game->setPageTitle(_('NPC Log'));
        $game->setTemplateVar('NPC_LOGS', $logs);
        $game->setTemplateVar('NPC_LOG_TYPES', $logTypes);
        $game->setTemplateVar('NPC_LOG_TYPE_COUNT', count($logTypes));
        $game->setTemplateVar('NPC_LOG_TYPE', $type);
        $game->setTemplateVar('NPC_LOG_COUNT', $count);
        $game->setTemplateVar('NPC_LOG_SEARCH', $search);
        $game->setTemplateVar('NPC_LOG_USER_ID', $sourceUserId === 0 ? '' : $sourceUserId);
        $game->setTemplateVar('NPC_LOG_ADMIN_VIEW', $includeAdminView);
        $game->setTemplateVar('NPC_LOG_CAN_SHOW_ADMIN_VIEW', $game->isAdmin());
        $game->setTemplateVar('NPC_LOG_EMPTY_MESSAGE', $type === self::TYPE_FACTION ? _('Keine Fraktions-Logs vorhanden') : _('Keine NPC-Logs vorhanden'));
    }
}
