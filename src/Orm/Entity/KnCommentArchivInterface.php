<?php

namespace Stu\Orm\Entity;

interface KnCommentArchivInterface
{
    public function getId(): int;

    public function getVersion(): ?string;

    public function getFormerId(): int;

    public function getKnId(): int;

    public function setPostId(int $postId): KnCommentArchivInterface;

    public function getUserId(): int;

    public function getUsername(): string;

    public function setUsername(string $username): KnCommentArchivInterface;

    public function getText(): string;

    public function setText(string $text): KnCommentArchivInterface;

    public function getDate(): int;

    public function setDate(int $date): KnCommentArchivInterface;

    public function getPosting(): KnPostArchivInterface;

    public function setPosting(KnPostArchivInterface $post): KnCommentArchivInterface;

    public function setDeleted(int $timestamp): KnCommentArchivInterface;

    public function isDeleted(): bool;
}
