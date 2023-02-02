<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use JBBCode\Parser;
use Stu\Orm\Entity\MapBorderTypeInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

/**
 * @Entity
 */
class ExploreableStarMap implements ExploreableStarMapInterface
{
    /** @Id @Column(type="integer") * */
    private int $id = 0;

    /** @Column(type="integer") * */
    private int $cx = 0;

    /** @Column(type="integer") * */
    private int $cy = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $field_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $bordertype_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $user_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $mapped = 0;

    /** @Column(type="string", nullable=true) * */
    private ?string $system_name;

    /** @Column(type="integer", nullable=true) * */
    private ?int $influence_area_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $region_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $tradepost_id;

    private $tradepost;

    private bool $hide = false;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\MapBorderType")
     * @JoinColumn(name="bordertype_id", referencedColumnName="id")
     * @var null|MapBorderTypeInterface
     */
    private ?MapBorderTypeInterface $mapBorderType;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\StarSystem")
     * @JoinColumn(name="influence_area_id", referencedColumnName="id")
     * @var null|StarSystemInterface
     */
    private ?StarSystemInterface $influenceArea;

    /**
     * @ManyToOne(targetEntity="Stu\Orm\Entity\MapRegion")
     * @JoinColumn(name="region_id", referencedColumnName="id")
     * @var null|MapRegionInterface
     */
    private ?MapRegionInterface $adminRegion;

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

    private function getMapped(): ?int
    {
        return $this->mapped;
    }

    public function getTitle(): ?string
    {
        if ($this->hide === true) {
            return null;
        }

        $tradepost = $this->getTradepost();

        return sprintf(
            '%s%s%s',
            $tradepost !== null ? $this->getTradepostTitle($tradepost) : '',
            $tradepost !== null && $this->mapped !== null ? ' über ' : '',
            $this->mapped !== null ? $this->system_name . '-System' : ''
        );
    }

    private function getTradepostTitle(TradePostInterface $tradepost): string
    {
        $licenseInfo = $tradepost->getLatestLicenseInfo();

        if ($licenseInfo === null) {
            return $this->getStringWithoutBbCode($tradepost->getName());
        }

        return sprintf(
            '%s (Lizenz für %d Tage: %d %s)',
            $this->getStringWithoutBbCode($tradepost->getName()),
            $licenseInfo->getDays(),
            $licenseInfo->getAmount(),
            $licenseInfo->getCommodity()->getName()
        );
    }

    private function getStringWithoutBbCode(string $string): string
    {
        // @todo refactor
        global $container;

        $parser = $container->get(Parser::class);

        return  $parser->parse($string)->getAsText();
    }

    public function getIcon(): ?string
    {
        if ($this->hide === true) {
            return null;
        }

        $tradepost = $this->getTradepost();

        if ($tradepost === null && $this->mapped === null) {
            return null;
        }

        return sprintf(
            '%s%s',
            $tradepost !== null && !$tradepost->isNpcTradepost() ? 'tradepost' : '',
            $this->mapped ? 'mapped' : ''
        );
    }

    public function getHref(): ?string
    {
        return $this->mapped ? sprintf('database.php?SHOW_ENTRY=1&cat=7&ent=%d', $this->getMapped()) : null;
    }

    private function getTradepost(): ?TradePostInterface
    {
        if ($this->tradepost_id === null) {
            return null;
        }

        if ($this->tradepost === null) {
            // @todo refactor
            global $container;

            $this->tradepost = $container->get(TradePostRepositoryInterface::class)->find($this->tradepost_id);
        }

        return $this->tradepost;
    }


    public function setHide(bool $hide): ExploreableStarMapInterface
    {
        $this->hide = $hide;

        return $this;
    }

    private function getBorder(): string
    {
        $borderType = $this->mapBorderType;
        if ($borderType === null) {
            if ($this->adminRegion === null) {
                if ($this->influenceArea !== null) {
                    $influenceArea = $this->influenceArea;
                    $base = $influenceArea->getBase();

                    if ($base !== null) {
                        $user = $base->getUser();
                        $ally = $user->getAlliance();

                        if ($ally !== null && strlen($ally->getRgbCode()) > 0) {
                            return 'border: 1px solid ' . $ally->getRgbCode();
                        } else if (strlen($user->getRgbCode()) > 0) {
                            return 'border: 1px solid ' . $user->getRgbCode();
                        }
                    }
                }
            }
        } else {
            return 'border: 1px solid ' . $borderType->getColor();
        }

        return '';
    }
    public function getFieldStyle(): string
    {
        if ($this->hide === true) {
            $type = 0;
        } else {
            $type = $this->getFieldId();
        }

        $style = "background-image: url('assets/map/" . $type . ".png');";
        $style .= $this->getBorder();
        return $style;
    }
}
