<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Table(
 *     name="stu_database_entrys",
 *     options={"engine":"InnoDB"},
 *     indexes={@Index(name="database_entry_category_id_idx", columns={"category_id"})}
 * )
 * @Entity(repositoryClass="Stu\Orm\Repository\DatabaseEntryRepository")
 **/
final class DatabaseEntry implements DatabaseEntryInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") * */
    private $description;

    /** @Column(type="text") * */
    private $data;

    /** @Column(type="integer") * */
    private $category_id;

    /** @Column(type="integer") * */
    private $type;

    /** @Column(type="integer") * */
    private $sort;

    /** @Column(type="integer") * */
    private $object_id;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\DatabaseType")
     * @JoinColumn(name="type", referencedColumnName="id")
     */
    private $type_object;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\DatabaseCategory", inversedBy="entries")
     * @JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseEntryInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setData(string $data): DatabaseEntryInterface
    {
        $this->data = $data;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function setCategory(DatabaseCategoryInterface $category): DatabaseEntryInterface
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): DatabaseCategoryInterface
    {
        return $this->category;
    }

    public function setSort(int $sort): DatabaseEntryInterface
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setObjectId(int $objectId): DatabaseEntryInterface
    {
        $this->object_id = $objectId;

        return $this;
    }

    public function getObjectId(): int
    {
        return $this->object_id;
    }

    public function getTypeObject(): DatabaseTypeInterface
    {
        return $this->type_object;
    }

    public function setTypeObject(DatabaseTypeInterface $typeObject): DatabaseEntryInterface
    {
        $this->type_object = $typeObject;

        return $this;
    }
}
