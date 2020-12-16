<?php

namespace Stu\Module\Control;

interface NotificationInterface
{
    public function getText(): string;

    public function setText(string $text): NotificationInterface;

    public function getLink(): ?string;

    public function setLink(?string $link): NotificationInterface;
}
