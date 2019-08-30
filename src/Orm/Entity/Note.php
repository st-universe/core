<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\NoteRepository")
 * @Table(name="stu_notes",indexes={@Index(name="user_idx", columns={"user_id"})})
 **/
class Note implements NoteInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="integer") * */
    private $date;

    /** @Column(type="string") * */
    private $title;

    /** @Column(type="text") * */
    private $text;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(int $userId): NoteInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setDate(int $date): NoteInterface
    {
        $this->date = $date;

        return $this;
    }

    public function getDate(): int
    {
        return $this->date;
    }

    public function setTitle(string $title): NoteInterface
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setText(string $text): NoteInterface
    {
        $this->text = $text;

        return $this;
    }

    public function getText(): string
    {
        return $this->text;
    }
}
