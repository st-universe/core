<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchiveComments;

use Override;
use Stu\Orm\Entity\KnCommentArchiv;

final class KnCommentArchivWrapper implements KnCommentArchivWrapperInterface
{
    public function __construct(
        private readonly KnCommentArchiv $comment
    ) {}

    #[Override]
    public function getId(): int
    {
        return $this->comment->getId();
    }

    #[Override]
    public function getFormerId(): int
    {
        return $this->comment->getFormerId();
    }

    #[Override]
    public function getKnId(): int
    {
        return $this->comment->getKnId();
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
        return $this->comment->getUserId();
    }

    #[Override]
    public function getDisplayUserName(): string
    {
        return $this->comment->getUsername();
    }

    #[Override]
    public function getUserAvatarPath(): string
    {
        return '';
    }

    #[Override]
    public function isDeleteable(): bool
    {
        return false;
    }

    #[Override]
    public function getVersion(): string
    {
        return $this->comment->getVersion() ?? '';
    }
}
