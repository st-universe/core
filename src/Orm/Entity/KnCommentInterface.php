<?php

namespace Stu\Orm\Entity;

interface KnCommentInterface
{
    public function getId(): int;

    public function getKnId(): int;

    public function setPostId(int $postId): KnCommentInterface;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): KnCommentInterface;

    public function getUsername(): string;

    public function setUsername(string $username): KnCommentInterface;

    public function getText(): string;

    public function setText(string $text): KnCommentInterface;

    public function getDate(): int;

    public function setDate(int $date): KnCommentInterface;

    public function getPosting(): KnPostInterface;

    public function setPosting(KnPostInterface $post): KnCommentInterface;

    public function setDeleted(int $timestamp): KnCommentInterface;

    public function isDeleted(): bool;
}
