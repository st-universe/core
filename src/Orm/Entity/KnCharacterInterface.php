<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

interface KnCharacterInterface
{
    public function getId(): int;

    public function getKnId(): int;

    public function setKnId(int $knId): KnCharacterInterface;

    public function getCharacterId(): int;

    public function setCharacterId(int $characterId): KnCharacterInterface;

    public function getKnPost(): KnPostInterface;

    public function setKnPost(KnPostInterface $knPost): KnCharacterInterface;

    public function getUserCharacter(): UserCharacterInterface;

    public function setUserCharacter(UserCharacterInterface $userCharacters): KnCharacterInterface;
}
