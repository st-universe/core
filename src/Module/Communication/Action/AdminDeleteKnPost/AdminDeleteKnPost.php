<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AdminDeleteKnPost;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class AdminDeleteKnPost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADMIN_DEL_KN';

    public function __construct(private AdminDeleteKnPostRequestInterface $deleteKnPostRequest, private KnPostRepositoryInterface $knPostRepository, private PrivateMessageSenderInterface $privateMessageSender) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolationException();
        }
        $admin = $game->getUser();

        /** @var KnPost|null $post */
        $post = $this->knPostRepository->find($this->deleteKnPostRequest->getKnId());
        if (!$post) {
            throw new AccessViolationException();
        }


        $reason = $this->deleteKnPostRequest->getReason() !== '' ?
            sprintf(_('Begründung: %s'), $this->deleteKnPostRequest->getReason()) :
            '';
        $user = $post->getUser();
        $text = sprintf(_('Der KN-Beitrag mit der ID %d wurde vom Admin %s gelöscht. %s'), $post->getId(), $admin->getName(), $reason);
        $this->sendPm($text, $user);

        $post->setDeleted(time());
        $this->knPostRepository->save($post);
        $game->getInfo()->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    private function sendPm(string $text, User $user): void
    {

        $this->privateMessageSender->send(
            UserConstants::USER_NOONE,
            $user->getId(),
            $text
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
