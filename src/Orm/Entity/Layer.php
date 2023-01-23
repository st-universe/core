<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

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
     * @var int
     */
    private $id;

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $name;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $width;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $height;

    /**
     * @Column(type="boolean")
     *
     * @var bool
     */
    private $is_hidden;

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
}
