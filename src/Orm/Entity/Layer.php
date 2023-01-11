<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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
     */
    private $id;

    /** @Column(type="string") * */
    private $name;

    /** @Column(type="integer") * */
    private $width;

    /** @Column(type="integer") * */
    private $height;

    /** @Column(type="boolean") */
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
