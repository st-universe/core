<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AnomalyRepository")
 * @Table(
 *     name="stu_anomaly",
 *     indexes={
 *         @Index(name="anomaly_to_type_idx", columns={"anomaly_type_id"})
 *     }
 * )
 **/
class Anomaly implements AnomalyInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $remaining_ticks;

    /**
     * @Column(type="integer")
     *
     */
    private int $anomaly_type_id;

    /**
     * @ManyToOne(targetEntity="AnomalyType")
     * @JoinColumn(name="anomaly_type_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private AnomalyTypeInterface $anomalyType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRemainingTicks(): int
    {
        return $this->remaining_ticks;
    }

    public function setRemainingTicks(int $remainingTicks): AnomalyInterface
    {
        $this->remaining_ticks = $remainingTicks;

        return $this;
    }

    public function getAnomalyType(): AnomalyTypeInterface
    {
        return $this->anomalyType;
    }

    public function setAnomalyType(AnomalyTypeInterface $anomalyType): AnomalyInterface
    {
        $this->anomalyType = $anomalyType;

        return $this;
    }
}
