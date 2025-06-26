<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\KnCharacterRepository;

#[Table(name: "stu_kn_character")]
#[Entity(repositoryClass: KnCharacterRepository::class)]
class KnCharacter
{
    #[Id]
    #[Column(type: "integer")]
    #[GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[Column(name: "kn_id", type: "integer")]
    private int $knId;

    #[Column(name: "character_id", type: "integer")]
    private int $characterId;

    #[ManyToOne(targetEntity: KnPost::class)]
    #[JoinColumn(name: "kn_id", nullable: false, referencedColumnName: "id", onDelete: "CASCADE")]
    private KnPost $knPost;

    #[ManyToOne(targetEntity: UserCharacter::class)]
    #[JoinColumn(name: "character_id", nullable: false, referencedColumnName: "id", onDelete: "CASCADE")]
    private UserCharacter $userCharacters;


    public function getId(): int
    {
        return $this->id;
    }

    public function getKnId(): int
    {
        return $this->knId;
    }

    public function setKnId(int $knId): KnCharacter
    {
        $this->knId = $knId;
        return $this;
    }

    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    public function setCharacterId(int $characterId): KnCharacter
    {
        $this->characterId = $characterId;
        return $this;
    }

    public function getKnPost(): KnPost
    {
        return $this->knPost;
    }

    public function setKnPost(KnPost $knPost): KnCharacter
    {
        $this->knPost = $knPost;
        return $this;
    }

    public function getUserCharacter(): UserCharacter
    {
        return $this->userCharacters;
    }

    public function setUserCharacter(UserCharacter $userCharacters): KnCharacter
    {
        $this->userCharacters = $userCharacters;
        return $this;
    }
}
