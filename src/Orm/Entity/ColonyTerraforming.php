<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Colony;
use ColonyData;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyTerraformingRepository")
 * @Table(
 *     name="stu_colonies_terraforming",
 *     indexes={
 *          @Index(name="colony_idx",columns={"colonies_id"}),
 *          @Index(name="finished_idx",columns={"finished"})
 *     }
 * )
 */
class ColonyTerraforming implements ColonyTerraformingInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $colonies_id = 0;

    /** @Column(type="integer") * */
    private $field_id = 0;

    /** @Column(type="integer") * */
    private $terraforming_id = 0;

    /** @Column(type="integer") * */
    private $finished = 0;

    /**
     * @ManyToOne(targetEntity="Terraforming")
     * @JoinColumn(name="terraforming_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $terraforming;

    /**
     * @ManyToOne(targetEntity="PlanetField")
     * @JoinColumn(name="field_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $field;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colonies_id;
    }

    public function setColonyId(int $colonyId): ColonyTerraformingInterface
    {
        $this->colonies_id = $colonyId;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getTerraformingId(): int
    {
        return $this->terraforming_id;
    }

    public function setTerraformingId(int $terraformingId): ColonyTerraformingInterface
    {
        $this->terraforming_id = $terraformingId;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finished;
    }

    public function setFinishDate(int $finishDate): ColonyTerraformingInterface
    {
        $this->finished = $finishDate;

        return $this;
    }

    public function getTerraforming(): TerraformingInterface
    {
        return $this->terraforming;
    }

    public function setTerraforming(TerraformingInterface $terraforming): ColonyTerraformingInterface
    {
        $this->terraforming = $terraforming;

        return $this;
    }

    public function getField(): PlanetFieldInterface
    {
        return $this->field;
    }

    public function setField(PlanetFieldInterface $planetField): ColonyTerraformingInterface
    {
        $this->field = $planetField;

        return $this;
    }

    public function getColony(): ColonyData
    {
        return new Colony($this->getColonyId());
    }

    public function getProgress(): int
    {
        $start = $this->getFinishDate() - $this->getTerraforming()->getDuration();
        return time() - $start;
    }
}
