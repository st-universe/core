<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface KnCharactersInterface
{
    public function getId(): int;

    public function getKnId(): int;

    public function setKnId(int $knId): KnCharactersInterface;

    public function getCharacterId(): int;

    public function setCharacterId(int $characterId): KnCharactersInterface;

    public function getKnPost(): KnPostInterface;

    public function setKnPost(KnPostInterface $knPost): KnCharactersInterface;

    public function getUserCharacters(): UserCharactersInterface;

    public function setUserCharacters(UserCharactersInterface $userCharacters): KnCharactersInterface;
}
