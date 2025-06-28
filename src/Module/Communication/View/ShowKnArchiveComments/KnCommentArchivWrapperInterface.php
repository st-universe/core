<?php

namespace Stu\Module\Communication\View\ShowKnArchiveComments;

interface KnCommentArchivWrapperInterface
{
    public function getId(): int;

    public function getFormerId(): int;

    public function getKnId(): int;

    public function getText(): string;

    public function getDate(): int;

    public function getUserId(): int;

    public function getDisplayUserName(): string;

    public function getUserAvatarPath(): string;

    public function isDeleteable(): bool;

    public function getVersion(): string;
}
