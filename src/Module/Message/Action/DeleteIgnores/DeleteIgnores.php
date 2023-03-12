<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteIgnores;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\IgnoreListInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;

final class DeleteIgnores implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_IGNORES';

    private DeleteIgnoresRequestInterface $deleteIgnoresRequest;

    private IgnoreListRepositoryInterface $ignoreListRepository;

    public function __construct(
        DeleteIgnoresRequestInterface $deleteIgnoresRequest,
        IgnoreListRepositoryInterface $ignoreListRepository
    ) {
        $this->deleteIgnoresRequest = $deleteIgnoresRequest;
        $this->ignoreListRepository = $ignoreListRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        foreach ($this->deleteIgnoresRequest->getIgnoreIds() as $val) {
            /** @var IgnoreListInterface $contact */
            $contact = $this->ignoreListRepository->find($val);
            if (!$contact || !$contact->getUserId() != $userId) {
                continue;
            }

            $this->ignoreListRepository->delete($contact);
        }
        $game->addInformation(_('Die Einträge wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
