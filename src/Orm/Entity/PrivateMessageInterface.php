<?php

namespace Stu\Orm\Entity;

use User;

interface PrivateMessageInterface
{
    public function getId(): int;

    public function getSenderId(): int;

    public function setSenderId(int $senderId): PrivateMessageInterface;

    public function getRecipientId(): int;

    public function setRecipientId(int $recipientId): PrivateMessageInterface;

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

    public function getCategory(): PrivateMessageFolderInterface;

    public function setCategory(PrivateMessageFolderInterface $folder): PrivateMessageInterface;

    public function isMarkableAsNew(): bool;

    public function getSender(): User;

    public function getRecipient(): User;

    public function copyPM(): void;

    public function senderIsIgnored(): bool;

    public function senderIsContact(): ?ContactInterface;

    public function displayUserLinks(): bool;
}