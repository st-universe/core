<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\KnPostToPlotApplicationRepository")
 * @Table(
 *     name="stu_kn_plot_application"
 * )
 **/
class KnPostToPlotApplication implements KnPostToPlotApplicationInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $post_id = 0;

    /** @Column(type="integer") */
    private $plot_id = 0;

    /** @Column(type="integer") */
    private $time = 0;

    /**
     * @ManyToOne(targetEntity="KnPost")
     * @JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $knPost;

    /**
     * @ManyToOne(targetEntity="RpgPlot")
     * @JoinColumn(name="plot_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $rpgPlot;

    public function getId(): int
    {
        return $this->id;
    }

    public function getRpgPlot(): RpgPlotInterface
    {
        return $this->rpgPlot;
    }

    public function setRpgPlot(RpgPlotInterface $rpgPlot): KnPostToPlotApplicationInterface
    {
        $this->rpgPlot = $rpgPlot;

        return $this;
    }

    public function getKnPost(): KnPostInterface
    {
        return $this->knPost;
    }

    public function setKnPost(KnPostInterface $knPost): KnPostToPlotApplicationInterface
    {
        $this->knPost = $knPost;

        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }
    public function setTime(int $time): KnPostToPlotApplicationInterface
    {
        $this->time = $time;
        return $this;
    }
}
