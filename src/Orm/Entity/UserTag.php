<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DateTimeInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\UserTagRepository")
 * @Table(name="stu_user_tag")
 **/
class UserTag implements UserTagInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $user_id;

    /** @Column(type="integer") * */
    private $tag_type_id;

    /** @Column(type="datetime") * */
    private $date;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function setTagTypeId(int $tagTypeId): UserTagInterface {
        $this->tag_type_id = $tagTypeId;
        return $this;
    }

    public function getTagTypeId(): int
    {
        return $this->tag_type_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): UserTagInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): UserTagInterface
    {
        $this->date = $date;

        return $this;
    }

}
