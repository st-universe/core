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
    private $id = 0;

    /** @Column(type="integer") * */
    private $cx = 0;

    /** @Column(type="integer") * */
    private $cy = 0;

    /** @Column(type="integer", nullable=true) * */
    private $field_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $bordertype_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $user_id = 0;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\MapBorderType")
     * @JoinColumn(name="bordertype_id", referencedColumnName="id")
     * @var null|MapBorderTypeInterface
     */
    private $mapBorderType;

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

    private $hide = false;

    public function setHide(bool $hide): ExploreableStarMapInterface
    {
        $this->hide = $hide;

        return $this;
    }

    public function getFieldStyle(): string
    {
        if ($this->mapBorderType === null) {
            $borderStyle = '';
        } else {
            $borderStyle = 'border: 1px solid #' . $this->mapBorderType->getColor();
        }

        if ($this->hide === true) {
            $type = 0;
        } else {
            $type = $this->getFieldId();
        }

        $style = "background-image: url('assets/map/" . $type . ".gif');";
        $style .= $borderStyle;
        return $style;
    }
}