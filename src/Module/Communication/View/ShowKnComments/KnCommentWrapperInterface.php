<?php

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Orm\Entity\UserInterface;

interface KnCommentWrapperInterface
{
    public function getId(): int;

    public function getPostId(): int;

    public function getText(): string;

    public function getDate(): int;

    public function getUserId(): int;

    public function getDisplayUserName(): string;

    public function getUserAvatarPath(): string;

    public function isDeleteable(): bool;

    public function getUser(): UserInterface;
}
