<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllIgnores;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class DeleteAllIgnores implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ALL_IGNORES';

    public function __construct(private IgnoreListRepositoryInterface $ignoreListRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $this->ignoreListRepository->truncateByUser($game->getUser()->getId());

        $game->getInfo()->addInformation(_('Die Einträge wurden gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
