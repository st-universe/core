<?php

declare(strict_types=1);

namespace Stu\Module\Database\View;

use request;
use Stu\Control\GameController;
use Stu\Control\ViewControllerInterface;
use User;

final class DisplayUserList implements ViewControllerInterface
{

    public function handle(GameController $game): void
    {
        $game->appendNavigationPart(
            "database.php?SHOW_SETTLERLIST=1",
            "Siedlerliste"
        );
        $game->setPageTitle("/ Siedlerliste");
        $game->setTemplateFile('html/userlist.xhtml');

        $game->setTemplateVar('NAVIGATION', $this->getUserListNavigation($game));
        $game->setTemplateVar('LIST', $this->getUserList());
        $game->setTemplateVar('SORT_ORDER', $this->getUserListWay());
        $game->setTemplateVar('ORDER_BY', $this->getUserListOrder());
        $game->setTemplateVar('PAGINATION', $this->getUserListMark());
    }

    private function getUserList()
    {
        switch ($this->getUserListOrder()) {
            case 'id':
                $sort = 'id';
                break;
            case "fac":
                $sort = 'race';
                break;
            case "alliance":
                $sort = 'allys_id';
                break;
            default:
                $sort = 'id';
        }
        switch ($this->getUserListWay()) {
            case 'up':
                $soway = 'DESC';
                break;
            case 'down':
                $soway = 'ASC';
                break;
            default:
                $soway = 'ASC';
        }
        return User::getListBy("WHERE id>100 ORDER BY " . $sort . " " . $soway . " LIMIT " . $this->getUserListMark() . "," . USERLISTLIMITER);
    }

    private function getUserListOrder()
    {
        return request::getString('order');
    }

    private function getUserListWay()
    {
        return request::getString('way');
    }

    private function getUserListMark()
    {
        return request::getInt('mark', 0);
    }

    private function getUserListNavigation(GameController $game)
    {
        $mark = $this->getUserListMark();
        if ($mark % USERLISTLIMITER != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $game->getPlayerCount();
        $maxpage = ceil($maxcount / USERLISTLIMITER);
        $curpage = floor($mark / USERLISTLIMITER);
        $ret = array();
        if ($curpage != 0) {
            $ret[] = array("page" => "<<", "mark" => 0, "cssclass" => "pages");
            $ret[] = array("page" => "<", "mark" => ($mark - USERLISTLIMITER), "cssclass" => "pages");
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $ret[] = array(
                "page" => $i,
                "mark" => ($i * USERLISTLIMITER - USERLISTLIMITER),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages")
            );
        }
        if ($curpage + 1 != $maxpage) {
            $ret[] = array("page" => ">", "mark" => ($mark + USERLISTLIMITER), "cssclass" => "pages");
            $ret[] = array(
                "page" => ">>",
                "mark" => $maxpage * USERLISTLIMITER - USERLISTLIMITER,
                "cssclass" => "pages"
            );
        }
        return $ret;
    }
}