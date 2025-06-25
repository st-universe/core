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
use Override;
use Stu\Orm\Repository\KnCharacterRepository;

#[Table(name: "stu_kn_character")]
#[Entity(repositoryClass: KnCharacterRepository::class)]
class KnCharacter implements KnCharacterInterface
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
    private KnPostInterface $knPost;

    #[ManyToOne(targetEntity: UserCharacter::class)]
    #[JoinColumn(name: "character_id", nullable: false, referencedColumnName: "id", onDelete: "CASCADE")]
    private UserCharacterInterface $userCharacters;


    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getKnId(): int
    {
        return $this->knId;
    }

    #[Override]
    public function setKnId(int $knId): KnCharacterInterface
    {
        $this->knId = $knId;
        return $this;
    }

    #[Override]
    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    #[Override]
    public function setCharacterId(int $characterId): KnCharacterInterface
    {
        $this->characterId = $characterId;
        return $this;
    }

    #[Override]
    public function getKnPost(): KnPostInterface
    {
        return $this->knPost;
    }

    #[Override]
    public function setKnPost(KnPostInterface $knPost): KnCharacterInterface
    {
        $this->knPost = $knPost;
        return $this;
    }

    #[Override]
    public function getUserCharacter(): UserCharacterInterface
    {
        return $this->userCharacters;
    }

    #[Override]
    public function setUserCharacter(UserCharacterInterface $userCharacters): KnCharacterInterface
    {
        $this->userCharacters = $userCharacters;
        return $this;
    }
}
