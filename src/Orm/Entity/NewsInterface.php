<?php

namespace Stu\Orm\Entity;

interface NewsInterface
{
    public function getId(): int;

    public function getSubject(): string;

    public function setSubject(int $subject): NewsInterface;

    public function getText(): string;

    public function getTextParsed(): string;

    public function setText(string $text): NewsInterface;

    public function getDate(): int;

    public function setDate(int $date): NewsInterface;

    public function getRefs(): string;

    public function setRefs(string $refs): NewsInterface;

    public function getLinks(): array;
}
