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
use Stu\Orm\Repository\KnPostToPlotApplicationRepository;

#[Table(name: 'stu_kn_plot_application')]
#[Entity(repositoryClass: KnPostToPlotApplicationRepository::class)]
class KnPostToPlotApplication
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $post_id = 0;

    #[Column(type: 'integer')]
    private int $plot_id = 0;

    #[Column(type: 'integer')]
    private int $time = 0;

    #[ManyToOne(targetEntity: KnPost::class)]
    #[JoinColumn(name: 'post_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private KnPost $knPost;

    #[ManyToOne(targetEntity: RpgPlot::class)]
    #[JoinColumn(name: 'plot_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private RpgPlot $rpgPlot;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRpgPlot(): RpgPlot
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(RpgPlot $rpgPlot): KnPostToPlotApplication
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    public function getKnPost(): KnPost
    {
        return $this->knPost;
    }

    public function setKnPost(KnPost $knPost): KnPostToPlotApplication
    {
        $this->knPost = $knPost;

        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }
    public function setTime(int $time): KnPostToPlotApplication
    {
        $this->time = $time;
        return $this;
    }
}
