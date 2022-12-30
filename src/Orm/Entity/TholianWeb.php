<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TholianWebRepository")
 * @Table(
 *     name="stu_tholian_web"
 * )
 **/
class TholianWeb implements TholianWebInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $finished_time = 0;

    /**
     * @OneToMany(targetEntity="Ship", mappedBy="holdingWeb", cascade={"remove"})
     */
    private $capturedShips;

    public function __construct()
    {
        $this->capturedShips = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCapturedShips(): Collection
    {
        return $this->capturedShips;
    }
}
