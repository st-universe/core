<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Orm\Entity\KnComment;
use Stu\Orm\Entity\User;

final class KnCommentWrapper implements KnCommentWrapperInterface
{
    public function __construct(
        private readonly ConfigInterface $config,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly KnComment $comment,
        private readonly User $currentUser
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->comment->getId();
    }

    #[Override]
    public function getKnId(): int
    {
        return $this->comment->getPosting()->getId();
    }

    #[Override]
    public function getText(): string
    {
        return $this->comment->getText();
    }

    #[Override]
    public function getDate(): int
    {
        return $this->comment->getDate();
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->comment->getUser()->getId();
    }

    #[Override]
    public function getDisplayUserName(): string
    {
        $commentUserName = $this->comment->getUserName();
        if ($commentUserName !== '') {
            return $commentUserName;
        }

        return $this->comment->getUser()->getName();
    }

    #[Override]
    public function getUserAvatarPath(): string
    {
        if ($this->comment->getUserName() !== '') {
            return '';
        }

        $userAvatar = $this->userSettingsProvider->getAvatar($this->comment->getUser());

        if ($userAvatar === '') {
            return sprintf(
                'assets/rassen/%skn.png',
                $this->comment->getUser()->getFactionId()
            );
        } else {

            return sprintf(
                '/%s/%s.png',
                $this->config->get('game.user_avatar_path'),
                $userAvatar
            );
        }
    }

    #[Override]
    public function isDeleteable(): bool
    {
        return $this->comment->getUser() === $this->currentUser;
    }

    #[Override]
    public function getUser(): User
    {
        return $this->comment->getUser();
    }
}
