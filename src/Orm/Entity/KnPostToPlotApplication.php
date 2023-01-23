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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $post_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $plot_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $time = 0;

    /**
     * @var KnPostInterface
     *
     * @ManyToOne(targetEntity="KnPost")
     * @JoinColumn(name="post_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $knPost;

    /**
     * @var RpgPlotInterface
     *
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
