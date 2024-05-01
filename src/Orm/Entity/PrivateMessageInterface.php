<?php

namespace Stu\Orm\Entity;

interface PrivateMessageInterface
{
    public function getId(): int;

    public function getSenderId(): int;

    public function getRecipientId(): int;

    public function getText(): string;

    public function setText(string $text): PrivateMessageInterface;

    public function getDate(): int;

    public function setDate(int $date): PrivateMessageInterface;

    public function getNew(): bool;

    public function setNew(bool $new): PrivateMessageInterface;

    public function getReplied(): bool;

    public function setReplied(bool $replied): PrivateMessageInterface;

    public function getCategoryId(): int;

    public function setCategoryId(int $categoryId): PrivateMessageInterface;

    public function getInboxPm(): ?PrivateMessageInterface;

    public function setInboxPm(?PrivateMessageInterface $pm): PrivateMessageInterface;

    public function getOutboxPm(): ?PrivateMessageInterface;

    public function getHref(): ?string;

    public function setHref(?string $href): PrivateMessageInterface;

    public function getCategory(): PrivateMessageFolderInterface;

    public function setCategory(PrivateMessageFolderInterface $folder): PrivateMessageInterface;

    public function getSender(): UserInterface;

    public function getRecipient(): UserInterface;

    public function setSender(UserInterface $user): PrivateMessageInterface;

    public function setRecipient(UserInterface $recipient): PrivateMessageInterface;

    public function isDeleted(): bool;

    public function setDeleted(int $timestamp): PrivateMessageInterface;

    public function hasTranslation(): bool;
}
