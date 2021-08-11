<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\UserList;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserList implements ViewControllerInterface
{

    public const VIEW_IDENTIFIER = 'SHOW_SETTLERLIST';

    private const SORT_FIELD_MAP = [
        'id' => 'id',
        'fac' => 'race',
        'alliance' => 'allys_id',
    ];

    private const SORT_ORDER_MAP = [
        'up' => 'DESC',
        'down' => 'ASC',
    ];

    private const LIST_LIMIT = 25;

    private UserListRequestInterface $userListRequest;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserListRequestInterface $userListRequest,
        UserRepositoryInterface $userRepository
    ) {
        $this->userListRequest = $userListRequest;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $sort_field = $this->userListRequest->getSortField();
        $sort_order = $this->userListRequest->getSortOrder();
        $pagination = $this->userListRequest->getPagination();

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                static::VIEW_IDENTIFIER,
            ),
            _('Siedlerliste')
        );
        $game->setPageTitle(_('/ Siedlerliste'));
        $game->setTemplateFile('html/userlist.xhtml');

        $user_list = $this->userRepository->getList(
            static::SORT_FIELD_MAP[$sort_field],
            static::SORT_ORDER_MAP[$sort_order],
            static::LIST_LIMIT,
            $pagination
        );

        $game->setTemplateVar('NAVIGATION', $this->getUserListNavigation($game));
        $game->setTemplateVar('LIST', $user_list);
        $game->setTemplateVar('SORT_ORDER', $sort_field);
        $game->setTemplateVar('ORDER_BY', $sort_order);
        $game->setTemplateVar('PAGINATION', $pagination);
    }

    private function getUserListNavigation(GameControllerInterface $game): array
    {
        $mark = $this->userListRequest->getPagination();
        if ($mark % static::LIST_LIMIT != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $game->getPlayerCount();
        $maxpage = ceil($maxcount / static::LIST_LIMIT);
        $curpage = floor($mark / static::LIST_LIMIT);
        $ret = [];
        if ($curpage != 0) {
            $ret[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $ret[] = ["page" => "<", "mark" => ($mark - static::LIST_LIMIT), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $ret[] = [
                "page" => $i,
                "mark" => ($i * static::LIST_LIMIT - static::LIST_LIMIT),
                "cssclass" => ($curpage + 1 == $i ? "pages selected" : "pages"),
            ];
        }
        if ($curpage + 1 != $maxpage) {
            $ret[] = ["page" => ">", "mark" => ($mark + static::LIST_LIMIT), "cssclass" => "pages"];
            $ret[] = [
                "page" => ">>",
                "mark" => $maxpage * static::LIST_LIMIT - static::LIST_LIMIT,
                "cssclass" => "pages",
            ];
        }
        return $ret;
    }
}
