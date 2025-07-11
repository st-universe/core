<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\UserList;

use JBBCode\Parser;
use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class UserList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SETTLERLIST';

    private const array SORT_FIELD_MAP = [
        'id' => 'id',
        'fac' => 'faction_id',
        'alliance' => 'allys_id',
    ];

    private const array SORT_ORDER_MAP = [
        'up' => 'DESC',
        'down' => 'ASC',
    ];

    private const int LIST_LIMIT = 25;

    public function __construct(
        private UserListRequestInterface $userListRequest,
        private UserRepositoryInterface $userRepository,
        private Parser $parser
    ) {}

    #[Override]
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
                self::VIEW_IDENTIFIER,
            ),
            _('Siedlerliste')
        );
        $game->setPageTitle(_('/ Siedlerliste'));
        $game->setViewTemplate('html/database/userList.twig');

        $search = request::indString('search');

        $user_list = $this->userRepository->getList(
            self::SORT_FIELD_MAP[$sort_field],
            self::SORT_ORDER_MAP[$sort_order],
            $search !== false ? null : self::LIST_LIMIT,
            $pagination
        );

        if ($search !== false) {
            $search = strtoupper($search);

            //filter by name/id
            $user_list = array_filter(
                $user_list,
                function (User $user) use ($search): bool {
                    $nameHit = strpos(strtoupper($this->parser->parse($user->getName())->getAsText()), $search) !== false;
                    $idHit = is_numeric($search) && ($user->getId() === (int)$search);

                    return $nameHit || $idHit;
                }
            );
        }

        $game->setTemplateVar('USER_LIST_NAVIGATION', $this->getUserListNavigation());
        $game->setTemplateVar('LIST', $user_list);
        $game->setTemplateVar('SORT_ORDER', $sort_order);
        $game->setTemplateVar('ORDER_BY', $sort_field);
        $game->setTemplateVar('PAGINATION', $pagination);
        $game->setTemplateVar('SEARCH', $search !== false ? request::indString('search') : '');
    }

    /** @return array<array{page: '<'|'<<'|'>', mark: int<-25, max>, cssclass: 'pages'}|array{page: '>>'|float, mark: float, cssclass: 'pages'|'pages selected'}> */
    private function getUserListNavigation(): array
    {
        $mark = $this->userListRequest->getPagination();
        if ($mark % self::LIST_LIMIT != 0 || $mark < 0) {
            $mark = 0;
        }
        $maxcount = $this->userRepository->getActiveAmount();
        $maxpage = ceil($maxcount / self::LIST_LIMIT);
        $curpage = floor($mark / self::LIST_LIMIT);
        $ret = [];
        if ($curpage != 0) {
            $ret[] = ["page" => "<<", "mark" => 0, "cssclass" => "pages"];
            $ret[] = ["page" => "<", "mark" => ($mark - self::LIST_LIMIT), "cssclass" => "pages"];
        }
        for ($i = $curpage - 1; $i <= $curpage + 3; $i++) {
            if ($i > $maxpage || $i < 1) {
                continue;
            }
            $ret[] = [
                "page" => $i,
                "mark" => ($i * self::LIST_LIMIT - self::LIST_LIMIT),
                "cssclass" => ($curpage + 1 === $i ? "pages selected" : "pages"),
            ];
        }
        if ($curpage + 1 !== $maxpage) {
            $ret[] = ["page" => ">", "mark" => ($mark + self::LIST_LIMIT), "cssclass" => "pages"];
            $ret[] = [
                "page" => ">>",
                "mark" => $maxpage * self::LIST_LIMIT - self::LIST_LIMIT,
                "cssclass" => "pages",
            ];
        }
        return $ret;
    }
}
