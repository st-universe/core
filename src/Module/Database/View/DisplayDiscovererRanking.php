<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use DatabaseTopListDiscover;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Lib\DbInterface;

final class DisplayDiscovererRanking implements ViewControllerInterface
{

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
            "database.php?SHOW_TOP_DISCOVER=1",
            ('Die 10 besten Entdecker')
        );
        $game->setPageTitle(_('/ Datenbank / Die 10 besten Entdecker'));
        $game->showAjaxMacro('html/database.xhtml/top_research_user');

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