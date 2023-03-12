<?php

namespace Stu\Orm\Entity;

interface NoteInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): NoteInterface;

    public function setDate(int $date): NoteInterface;

    public function getDate(): int;

    public function setTitle(string $title): NoteInterface;

    public function getTitle(): string;

    public function setText(string $text): NoteInterface;

    public function getText(): string;
}