<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TorpedoHullRepository")
 * @Table(
 *     name="stu_torpedo_hull",
 *     indexes={
 *         @Index(name="torpedo_hull_module_idx", columns={"module_id"}),
 *         @Index(name="torpedo_hull_torpedo_idx", columns={"torpedo_type"})
 *     }
 * )
 **/
class TorpedoHull implements TorpedoHullInterface
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $module_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $torpedo_type = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $modificator = 0;


    /**
     * @ManyToOne(targetEntity="TorpedoType")
     * @JoinColumn(name="torpedo_type", referencedColumnName="id")
     */
    private TorpedoTypeInterface $torpedo;

    /**
     * @ManyToOne(targetEntity="Module")
     * @JoinColumn(name="module_id", referencedColumnName="id")
     */
    private ModuleInterface $module;

    public function getId(): int
    {
        return $this->id;
    }

    public function getModuleId(): int
    {
        return $this->module_id;
    }

    public function setModuleId(int $moduleId): TorpedoHullInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getTorpedoType(): int
    {
        return $this->torpedo_type;
    }

    public function setTorpedoType(int $torpedoType): TorpedoHullInterface
    {
        $this->torpedo_type = $torpedoType;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): TorpedoHullInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getTorpedo(): TorpedoTypeInterface
    {
        return $this->torpedo;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function calculateGradientColor(): string
    {
        $color1 = '#00ff00';
        $color2 = '#ffd500';
        $color3 = '#FF0000';
        $percent = 100 / 29 * ($this->getModificator() - 88);

        // Konvertiere die Hex-Farbcodes in RGB-Werte
        $rgb1 = $this->hexToRgb($color1);
        $rgb2 = $this->hexToRgb($color2);
        $rgb3 = $this->hexToRgb($color3);

        // Verteile den Prozentwert zwischen den Farben
        if ($percent <= 50) {
            $gradientPercent = $percent * 2;
            $gradientRgb = $this->calculateGradientRgb($rgb1, $rgb2, $gradientPercent);
        } else {
            $gradientPercent = (($percent - 50) * 2);
            $gradientRgb = $this->calculateGradientRgb($rgb2, $rgb3, $gradientPercent);
        }

        // Konvertiere den RGB-Wert zurück in einen Hex-Farbcode
        $gradientColor = $this->rgbToHex($gradientRgb);

        return $gradientColor;
    }
    /**
     * @return array<int, int|float>
     */
    private function hexToRgb(string $color): array
    {
        $color = ltrim($color, '#');
        $length = strlen($color);
        $b = 0;
        $g = 0;
        $r = 0;
        if ($length == 3) {
            $r = hexdec(substr($color, 0, 1) . substr($color, 0, 1));
            $g = hexdec(substr($color, 1, 1) . substr($color, 1, 1));
            $b = hexdec(substr($color, 2, 1) . substr($color, 2, 1));
        } elseif ($length == 6) {
            $r = hexdec(substr($color, 0, 2));
            $g = hexdec(substr($color, 2, 2));
            $b = hexdec(substr($color, 4, 2));
        }

        return array($r, $g, $b);
    }

    /**
     * @param array<mixed> $rgb1
     * @param array<mixed> $rgb2
     * 
     * @return array<int>
     */
    private function calculateGradientRgb(array $rgb1, array $rgb2, float $percent): array
    {
        $r = intval($rgb1[0] + ($rgb2[0] - $rgb1[0]) * $percent / 100);
        $g = intval($rgb1[1] + ($rgb2[1] - $rgb1[1]) * $percent / 100);
        $b = intval($rgb1[2] + ($rgb2[2] - $rgb1[2]) * $percent / 100);

        return array($r, $g, $b);
    }

    /**
     * @param array<mixed> $rgb
     */
    private function rgbToHex(array $rgb): string
    {
        $r = str_pad(dechex((int) $rgb[0]), 2, '0', STR_PAD_LEFT);
        $g = str_pad(dechex((int) $rgb[1]), 2, '0', STR_PAD_LEFT);
        $b = str_pad(dechex((int) $rgb[2]), 2, '0', STR_PAD_LEFT);

        return '#' . $r . $g . $b;
    }
}