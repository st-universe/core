<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PartnerSiteRepository")
 * @Table(
 *     name="stu_partnersite",
 *     indexes={
 *     }
 * )
 **/
class PartnerSite implements PartnerSiteInterface
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
     * @Column(type="string")
     *
     * @var string
     */
    private $name = '';

    /**
     * @Column(type="string")
     *
     * @var string
     */
    private $url = '';

    /**
     * @Column(type="text")
     *
     * @var string
     */
    private $text = '';

    /**
     * @Column(type="string", length=200)
     *
     * @var string
     */
    private $banner = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getBanner(): string
    {
        return $this->banner;
    }
}
