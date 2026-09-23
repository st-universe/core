<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\UserList;

use JBBCode\Parser;
use request;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\Component\View\ViewControllerContext;
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
        private readonly UserListRequestInterface $userListRequest,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PagingFactory $pagingFactory,
        private readonly Parser $parser
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
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

        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            $this->userRepository->getActiveAmount(),
            self::LIST_LIMIT,
            $this->userListRequest->getPagination(),
            sprintf("?SHOW_SETTLERLIST=1&order=%s&way=%s", $sort_field, $sort_order)
        ));
        $game->setTemplateVar('LIST', $user_list);
        $game->setTemplateVar('PAGINATION', $pagination);
        $game->setTemplateVar('SEARCH', $search !== false ? request::indString('search') : '');
    }
}
