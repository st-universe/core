<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use DatabaseUser;
use DatabaseUserData;
use Ship;
use StarSystem;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;

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

    public function setCategoryId(int $categoryId): DatabaseEntryInterface
    {
        $this->category_id = $categoryId;

        return $this;
    }

    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    public function getTypeObject(): DatabaseTypeInterface {
        return $this->type_object;
    }

    public function setTypeObject(DatabaseTypeInterface $type_object): DatabaseEntryInterface {
        $this->type_object = $type_object;

        return $this;
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

    /**
     * @todo Refactor this
     * @see \Stu\Module\Database\View\DatabaseEntry\DatabaseEntry
     */
    public function getObject() {
        switch ($this->getCategoryId()) {
            case DATABASE_CATEGORY_STARSYSTEMS:
                return new StarSystem($this->getObjectId());
                break;
            case DATABASE_CATEGORY_TRADEPOSTS:
                return new Ship($this->getObjectId());
                break;
        }

        return null;
    }

    public function isDiscoveredByCurrentUser(): bool {
        return DatabaseUser::checkEntry($this->getId(),currentUser()->getId());
    }

    public function getDBUserObject(): ?DatabaseUserData {
        if (!$this->isDiscoveredByCurrentUser()) {
            return null;
        }
        return DatabaseUser::getBy($this->getId(),currentUser()->getId());
    }

    public function getCategory(): DatabaseCategoryInterface {
        // @todo replace by entity
        global $container;
        return $container->get(DatabaseCategoryRepositoryInterface::class)->find($this->getCategoryId());
    }
}
