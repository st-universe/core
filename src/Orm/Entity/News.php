<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_news')]
#[Index(name: 'news_date_idx', columns: ['date'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\NewsRepository')]
class News implements NewsInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $subject = '';

    #[Column(type: 'text')]
    private string $text = '';

    #[Column(type: 'integer')]
    private int $date = 0;

    #[Column(type: 'text')]
    private string $refs = '';

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getSubject(): string
    {
        return $this->subject;
    }

    #[Override]
    public function setSubject(string $subject): NewsInterface
    {
        $this->subject = $subject;

        return $this;
    }

    #[Override]
    public function getText(): string
    {
        return $this->text;
    }

    #[Override]
    public function setText(string $text): NewsInterface
    {
        $this->text = $text;

        return $this;
    }

    #[Override]
    public function getDate(): int
    {
        return $this->date;
    }

    #[Override]
    public function setDate(int $date): NewsInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function getRefs(): string
    {
        return $this->refs;
    }

    #[Override]
    public function setRefs(string $refs): NewsInterface
    {
        $this->refs = $refs;

        return $this;
    }

    #[Override]
    public function getLinks(): array
    {
        if ($this->getRefs() === '') {
            return [];
        }
        return explode("\n", $this->getRefs());
    }
}
