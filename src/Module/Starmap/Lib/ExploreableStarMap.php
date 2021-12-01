<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Stu\Orm\Entity\MapBorderTypeInterface;

/**
 * @Entity
 */
class ExploreableStarMap implements ExploreableStarMapInterface
{
    /** @Id @Column(type="integer") * */
    private int $id = 0;

    /** @Column(type="integer") * */
    private int $cx = 0;

    /** @Column(type="integer") * */
    private int $cy = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $field_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $bordertype_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $user_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $mapped = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $influence_id = 0;

    private bool $hide = false;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\MapBorderType")
     * @JoinColumn(name="bordertype_id", referencedColumnName="id")
     * @var null|MapBorderTypeInterface
     */
    private ?MapBorderTypeInterface $mapBorderType;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCx(): int
    {
        return $this->cx;
    }

    public function getCy(): int
    {
        return $this->cy;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getBordertypeId(): ?int
    {
        return $this->bordertype_id;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getMapped(): ?int
    {
        return $this->mapped;
    }

    public function setHide(bool $hide): ExploreableStarMapInterface
    {
        $this->hide = $hide;

        return $this;
    }

    public function getFieldStyle(): string
    {
        if ($this->mapBorderType === null) {
            if ($this->influence_id === 53) {
                $borderStyle = 'border: 1px solid #800080';
            } if ($this->influence_id === 43) {
                $borderStyle = 'border: 1px solid #E6E6E6';
            } if ($this->influence_id === 145) {
                $borderStyle = 'border: 1px solid #E6E6E6';
            } else {
                $borderStyle = '';
            }
        } else {
            $borderStyle = 'border: 1px solid #' . $this->mapBorderType->getColor();
        }

        if ($this->hide === true) {
            $type = 0;
        } else {
            $type = $this->getFieldId();
        }

        $style = "background-image: url('assets/map/" . $type . ".png');";
        $style .= $borderStyle;
        return $style;
    }
}
