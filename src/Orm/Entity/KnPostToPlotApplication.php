<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_kn_plot_application')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\KnPostToPlotApplicationRepository')]
class KnPostToPlotApplication implements KnPostToPlotApplicationInterface
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

    #[ManyToOne(targetEntity: 'KnPost')]
    #[JoinColumn(name: 'post_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private KnPostInterface $knPost;

    #[ManyToOne(targetEntity: 'RpgPlot')]
    #[JoinColumn(name: 'plot_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private RpgPlotInterface $rpgPlot;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getRpgPlot(): RpgPlotInterface
    {
        return $this->rpgPlot;
    }

    #[Override]
    public function setRpgPlot(RpgPlotInterface $rpgPlot): KnPostToPlotApplicationInterface
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    #[Override]
    public function getKnPost(): KnPostInterface
    {
        return $this->knPost;
    }

    #[Override]
    public function setKnPost(KnPostInterface $knPost): KnPostToPlotApplicationInterface
    {
        $this->knPost = $knPost;

        return $this;
    }

    #[Override]
    public function getTime(): int
    {
        return $this->time;
    }
    #[Override]
    public function setTime(int $time): KnPostToPlotApplicationInterface
    {
        $this->time = $time;
        return $this;
    }
}
