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


#[Table(name: "stu_kn_characters")]
#[Entity(repositoryClass: 'Stu\Orm\Repository\KnCharactersRepository')]
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


    public function getId(): int
    {
        return $this->id;
    }

    public function getKnId(): int
    {
        return $this->knId;
    }

    public function setKnId(int $knId): KnCharactersInterface
    {
        $this->knId = $knId;
        return $this;
    }

    public function getCharacterId(): int
    {
        return $this->characterId;
    }

    public function setCharacterId(int $characterId): KnCharactersInterface
    {
        $this->characterId = $characterId;
        return $this;
    }

    public function getKnPost(): KnPostInterface
    {
        return $this->knPost;
    }

    public function setKnPost(KnPostInterface $knPost): KnCharactersInterface
    {
        $this->knPost = $knPost;
        return $this;
    }

    public function getUserCharacters(): UserCharactersInterface
    {
        return $this->userCharacters;
    }

    public function setUserCharacters(UserCharactersInterface $userCharacters): KnCharactersInterface
    {
        $this->userCharacters = $userCharacters;
        return $this;
    }
}
