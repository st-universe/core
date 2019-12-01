<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllIgnores;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class DeleteAllIgnores implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ALL_IGNORES';

    private IgnoreListRepositoryInterface $ignoreListRepository;

    public function __construct(
        IgnoreListRepositoryInterface $ignoreListRepository
    ) {
        $this->ignoreListRepository = $ignoreListRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->ignoreListRepository->truncateByUser($game->getUser()->getId());

        $game->addInformation(_('Die Einträge wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
