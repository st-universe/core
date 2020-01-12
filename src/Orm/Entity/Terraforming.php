<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TerraformingRepository")
 * @Table(
 *     name="stu_terraforming",
 *     uniqueConstraints={@UniqueConstraint(name="field_transformation_idx", columns={"v_feld", "z_feld"})}
 * )
 */
class Terraforming implements TerraformingInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") * */
    private $description = '';

    /** @Column(type="integer") * */
    private $ecost = 0;

    /** @Column(type="integer") * */
    private $v_feld = 0;

    /** @Column(type="integer") * */
    private $z_feld = 0;

    /** @Column(type="integer") * */
    private $duration = 0;

    /**
     * @OneToMany(targetEntity="TerraformingCost", mappedBy="terraforming")
     */
    private $costs;

    public function getId(): int
    {
        return $this->id;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): TerraformingInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getEnergyCosts(): int
    {
        return $this->ecost;
    }

    public function setEnergyCosts(int $energyCosts): TerraformingInterface
    {
        $this->ecost = $energyCosts;

        return $this;
    }

    public function getFromFieldTypeId(): int
    {
        return $this->v_feld;
    }

    public function setFromFieldTypeId(int $fromFieldTypeId): TerraformingInterface
    {
        $this->v_feld = $fromFieldTypeId;

        return $this;
    }

    public function getToFieldTypeId(): int
    {
        return $this->z_feld;
    }

    public function setToFieldTypeId(int $toFieldTypeId): TerraformingInterface
    {
        $this->z_feld = $toFieldTypeId;

        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): TerraformingInterface
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCosts(): Collection
    {
        return $this->costs;
    }
}
