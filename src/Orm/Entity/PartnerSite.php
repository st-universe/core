<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Noodlehaus\ConfigInterface;

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
     */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="string") */
    private $url = '';

    /** @Column(type="text") */
    private $text = '';

    /** @Column(type="string", length=200) */
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

    public function getFullBannerPath(): string
    {
        // @todo refactor
        global $container;

        $config = $container->get(ConfigInterface::class);

        return sprintf(
            '/%s/%s.png',
            $config->get('partner_banner_path'),
            $this->getBanner()
        );
    }
}
