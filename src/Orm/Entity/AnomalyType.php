<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AnomalyTypeRepository")
 * @Table(
 *     name="stu_anomaly_type"
 * )
 **/
class AnomalyType implements AnomalyTypeInterface
{
    /**
     * @Id
     * @Column(type="integer")
     */
    private int $id;

    /**
     * @Column(type="string", length=200)
     *
     */
    private string $name;

    /**
     * @Column(type="integer")
     *
     */
    private int $lifespan_in_ticks;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLifespanInTicks(): int
    {
        return $this->lifespan_in_ticks;
    }

    public function getTemplate(): string
    {
        return AnomalyTypeEnum::from($this->getId())->getTemplate();
    }
}
