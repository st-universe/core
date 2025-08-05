<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteIgnores;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class DeleteIgnores implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_IGNORES';

    public function __construct(private DeleteIgnoresRequestInterface $deleteIgnoresRequest, private IgnoreListRepositoryInterface $ignoreListRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        foreach ($this->deleteIgnoresRequest->getIgnoreIds() as $val) {
            $contact = $this->ignoreListRepository->find($val);
            if (!$contact || !$contact->getUserId() != $userId) {
                continue;
            }

            $this->ignoreListRepository->delete($contact);
        }
        $game->getInfo()->addInformation(_('Die Einträge wurden gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
