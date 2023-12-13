<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_plots_members_archiv')]
#[UniqueConstraint(name: 'plot_archiv_user_idx', columns: ['plot_id', 'user_id'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\RpgPlotMemberArchivRepository')]
class RpgPlotMemberArchiv implements RpgPlotMemberArchivInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $version = '';

    #[Column(type: 'integer')]
    private int $former_id = 0;

    #[Column(type: 'integer')]
    private int $plot_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[ManyToOne(targetEntity: 'RpgPlotArchiv', inversedBy: 'members')]
    #[JoinColumn(name: 'plot_id', referencedColumnName: 'former_id', onDelete: 'CASCADE')]
    private RpgPlotArchivInterface $rpgPlot;

    public function getId(): int
    {
        return $this->id;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getFormerId(): int
    {
        return $this->former_id;
    }

    public function getPlotId(): int
    {
        return $this->plot_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getRpgPlot(): RpgPlotArchivInterface
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(RpgPlotArchivInterface $rpgPlot): RpgPlotMemberArchivInterface
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }
}
