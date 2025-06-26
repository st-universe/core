<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\PartnerSiteRepository;

#[Table(name: 'stu_partnersite')]
#[Entity(repositoryClass: PartnerSiteRepository::class)]
class PartnerSite
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name = '';

    #[Column(type: 'string')]
    private string $url = '';

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'string', length: 200)]
    private string $banner = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getBanner(): string
    {
        return $this->banner;
    }
}
