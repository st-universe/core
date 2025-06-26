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
use Stu\Orm\Repository\RpgPlotMemberRepository;

#[Table(name: 'stu_plots_members')]
#[UniqueConstraint(name: 'plot_user_idx', columns: ['plot_id', 'user_id'])]
#[Entity(repositoryClass: RpgPlotMemberRepository::class)]
class RpgPlotMember
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $plot_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[ManyToOne(targetEntity: RpgPlot::class, inversedBy: 'members')]
    #[JoinColumn(name: 'plot_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private RpgPlot $rpgPlot;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPlotId(): int
    {
        return $this->plot_id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getRpgPlot(): RpgPlot
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(RpgPlot $rpgPlot): RpgPlotMember
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): RpgPlotMember
    {
        $this->user = $user;
        return $this;
    }
}
