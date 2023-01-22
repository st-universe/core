<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\NewsRepository")
 * @Table(
 *     name="stu_news",
 *     indexes={
 *         @Index(name="news_date_idx", columns={"date"})
 *     }
 * )
 **/
class News implements NewsInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") */
    private $subject = '';

    /** @Column(type="text") */
    private $text = '';

    /** @Column(type="integer") * */
    private $date = 0;

    /** @Column(type="text") */
    private $refs = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function setSubject(int $subject): NewsInterface
    {
        $this->subject = $subject;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): NewsInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setDate(int $date): NewsInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getRefs(): string
    {
        return $this->refs;
    }

    public function setRefs(string $refs): NewsInterface
    {
        $this->refs = $refs;

        return $this;
    }

    public function getLinks(): array
    {
        if ($this->getRefs() === '') {
            return [];
        }
        return explode("\n", $this->getRefs());
    }
}
