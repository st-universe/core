<?php

namespace Stu\Component\Index\News;

interface NewsItemInterface
{
    public function getSubject(): string;

    public function getText(): string;

    public function getDate(): int;

    public function getLinks(): array;
}
