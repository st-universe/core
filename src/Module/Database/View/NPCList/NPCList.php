<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\NPCList;

use JBBCode\Parser;
use request;
use Stu\Lib\Paging\PagingFactory;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

final class NPCList implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NPCLIST';

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
        private readonly NPCListRequestInterface $npcListRequest,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PagingFactory $pagingFactory,
        private readonly Parser $parser
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $sort_field = $this->npcListRequest->getSortField();
        $sort_order = $this->npcListRequest->getSortOrder();
        $pagination = $this->npcListRequest->getPagination();

        $game->appendNavigationPart(
            'database.php',
            _('Datenbank')
        );
        $game->appendNavigationPart(
            sprintf(
                'database.php?%s=1',
                self::VIEW_IDENTIFIER,
            ),
            _('NPC & Admin Liste')
        );
        $game->setPageTitle(_('/ NPC & Admin Liste'));
        $game->setViewTemplate('html/database/npcList.twig');

        $search = request::indString('search');

        $npc_list = $this->userRepository->getNPCAdminList(
            self::SORT_FIELD_MAP[$sort_field],
            self::SORT_ORDER_MAP[$sort_order],
            $search !== false ? null : self::LIST_LIMIT,
            $pagination
        );

        if ($search !== false) {
            $search = strtoupper($search);

            //filter by name/id
            $npc_list = array_filter(
                $npc_list,
                function (User $user) use ($search): bool {
                    $nameHit = strpos(strtoupper($this->parser->parse($user->getName())->getAsText()), $search) !== false;
                    $idHit = is_numeric($search) && ($user->getId() === (int)$search);

                    return $nameHit || $idHit;
                }
            );
        }

        $game->setTemplateVar('PAGING', $this->pagingFactory->createPaging(
            10,
            self::LIST_LIMIT,
            $this->npcListRequest->getPagination(),
            sprintf("?SHOW_NPCLIST=1&order=%s&way=%s", $sort_field, $sort_order)
        ));
        $game->setTemplateVar('LIST', $npc_list);
        $game->setTemplateVar('PAGINATION', $pagination);
        $game->setTemplateVar('SEARCH', $search !== false ? request::indString('search') : '');
    }
}
