<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\DiscovererRating;

use DatabaseTopListDiscover;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Lib\DbInterface;

final class DiscovererRanking implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_TOP_DISCOVER';

    private $db;

    public function __construct(
        DbInterface $db
    )
    {
        $this->db = $db;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Die 10 besten Entdecker')
        );
        $game->setPageTitle(_('/ Datenbank / Die 10 besten Entdecker'));
        $game->showMacro('html/database.xhtml/top_research_user');

        $game->setTemplateVar('DISCOVERER_LIST', $this->getTopResearchUser());
    }

    private function getTopResearchUser()
    {
        $list = [];
        $result = $this->db->query(
            'SELECT a.user_id,SUM(c.points) as points FROM stu_database_user as a LEFT JOIN stu_database_entrys as b ON b.id=a.database_id LEFT JOIN stu_database_categories as c ON c.id=b.category_id GROUP BY a.user_id ORDER BY points DESC LIMIT 10'
        );
        while ($entry = mysqli_fetch_assoc($result)) {
            $list[] = new DatabaseTopListDiscover($entry);
        }

        return $list;
    }
}