<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Communication\Action\EditKnPost\EditKnPost;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class DeleteKnPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEL_KN';

    public function __construct(private DeleteKnPostRequestInterface $deleteKnPostRequest, private KnPostRepositoryInterface $knPostRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = $this->knPostRepository->find($this->deleteKnPostRequest->getKnId());
        if ($post === null || $post->getUserId() !== $userId) {
            throw new AccessViolationException();
        }
        if ($post->getDate() < time() - EditKnPost::EDIT_TIME) {
            $game->addInformation(_('Dieser Beitrag kann nicht editiert werden'));
            return;
        }

        $post->setDeleted(time());
        $this->knPostRepository->save($post);

        $game->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
