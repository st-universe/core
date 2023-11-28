<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\UserList;

use JBBCode\Parser;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\UserInterface;
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

    private Parser $parser;

    public function __construct(
        UserListRequestInterface $userListRequest,
        UserRepositoryInterface $userRepository,
        Parser $parser
    ) {
        $this->userListRequest = $userListRequest;
        $this->userRepository = $userRepository;
        $this->parser = $parser;
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
        $game->setViewTemplate('html/database/userList.twig');

        $search = request::indString('search');

        $user_list = $this->userRepository->getList(
            static::SORT_FIELD_MAP[$sort_field],
            static::SORT_ORDER_MAP[$sort_order],
            $search !== false ? null : static::LIST_LIMIT,
            $pagination
        );

        if ($search !== false) {
            $search = strtoupper($search);

            //filter by name/id
            $user_list = array_filter(
                $user_list,
                function (UserInterface $user) use ($search): bool {
                    $nameHit = strpos(strtoupper($this->parser->parse($user->getName())->getAsText()), $search) !== false;
                    $idHit = is_numeric($search) && ($user->getId() === (int)$search);

                    return $nameHit || $idHit;
                }
            );
        }

        $game->setTemplateVar('NAVIGATION', $this->getUserListNavigation());
        $game->setTemplateVar('LIST', $user_list);
        $game->setTemplateVar('SORT_ORDER', $sort_field);
        $game->setTemplateVar('ORDER_BY', $sort_order);
        $game->setTemplateVar('PAGINATION', $pagination);
        $game->setTemplateVar('SEARCH', $search !== false ? request::indString('search') : '');
    }

    private function getUserListNavigation(): array
    {
        $mark = $this->userListRequest->getPagination();
        if ($mark % static::LIST_LIMIT != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $this->userRepository->getActiveAmount();
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
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages"),
            ];
        }
        if ($curpage + 1 !== $maxpage) {
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
