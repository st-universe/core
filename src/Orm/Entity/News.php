<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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

    public function getTextParsed(): string
    {
        // @todo refactor
        global $container;

        $parserWithImage = $container->get(ParserWithImageInterface::class);

        return $parserWithImage->parse($this->text)->getAsHTML();
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
