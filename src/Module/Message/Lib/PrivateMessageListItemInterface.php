<?php

namespace Stu\Module\Message\Lib;

use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\User;

interface PrivateMessageListItemInterface
{
    public function getSender(): User;

    public function getDate(): int;

    public function isMarkableAsNew(): bool;

    public function isMarkableAsReceipt(): bool;

    public function getText(): string;

    public function getHref(): ?string;

    public function getNew(): bool;

    public function getId(): int;

    public function displayUserLinks(): bool;

    public function senderIsContact(): ?Contact;

    public function hasTranslation(): bool;
}
