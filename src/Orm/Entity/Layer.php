<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Map\MapEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\LayerRepository")
 * @Table(
 *     name="stu_layer"
 * )
 **/
class Layer implements LayerInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="string")
     *
     */
    private string $name;

    /**
     * @Column(type="integer")
     *
     */
    private int $width;

    /**
     * @Column(type="integer")
     *
     */
    private int $height;

    /**
     * @Column(type="boolean")
     *
     */
    private bool $is_hidden;

    /**
     * @Column(type="boolean", nullable=true)
     *
     */
    private ?bool $is_finished = null;

    /**
     * @Column(type="boolean", nullable=true)
     *
     */
    private ?bool $is_encoded = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function isHidden(): bool
    {
        return $this->is_hidden;
    }

    public function isFinished(): bool
    {
        if ($this->is_finished === null) {
            return false;
        }

        return $this->is_finished;
    }

    public function isEncoded(): bool
    {
        if ($this->is_encoded === null) {
            return false;
        }

        return $this->is_encoded;
    }

    public function getSectorId(int $mapCx, int $mapCy): int
    {
        return $mapCx + ($mapCy - 1) * (int)ceil($this->getWidth() / MapEnum::FIELDS_PER_SECTION);
    }
}
