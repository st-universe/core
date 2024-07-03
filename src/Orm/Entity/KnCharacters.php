<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Repository\KnCharactersRepository;
use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;


#[Table(name: "stu_kn_characters")]
#[Entity(repositoryClass: KnCharactersRepository::class)]
class KnCharacters implements KnCharactersInterface
{
    #[Id]
    #[Column(type: "integer")]
    #[GeneratedValue(strategy: "IDENTITY")]
    private int $id;

    #[Column(name: "kn_id", type: "integer")]
    private int $knId;

    #[Column(name: "character_id", type: "integer")]
    private int $characterId;

    #[ManyToOne(targetEntity: "KnPost")]
    #[JoinColumn(name: "kn_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private KnPostInterface $knPost;

    #[ManyToOne(targetEntity: "UserCharacters")]
    #[JoinColumn(name: "character_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private UserCharactersInterface $userCharacters;


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
    public function setKnId(int $knId): KnCharactersInterface
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
    public function setCharacterId(int $characterId): KnCharactersInterface
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
    public function setKnPost(KnPostInterface $knPost): KnCharactersInterface
    {
        $this->knPost = $knPost;
        return $this;
    }

    #[Override]
    public function getUserCharacters(): UserCharactersInterface
    {
        return $this->userCharacters;
    }

    #[Override]
    public function setUserCharacters(UserCharactersInterface $userCharacters): KnCharactersInterface
    {
        $this->userCharacters = $userCharacters;
        return $this;
    }
}
