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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\WeaponShieldRepository")
 * @Table(
 *     name="stu_weapon_shield",
 *     indexes={
 *         @Index(name="weapon_shield_module_idx", columns={"module_id"}),
 *         @Index(name="weapon_shield_weapon_idx", columns={"weapon_id"})
 *     }
 * )
 **/
class WeaponShield implements WeaponShieldInterface
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
    private int $weapon_id = 0;

    /**
     * @Column(type="integer")
     *
     */
    private int $modificator = 0;

    /**
     * @Column(type="integer", nullable=true)
     * 
     */
    private int $faction_id = 0;

    /**
     * @ManyToOne(targetEntity="Weapon")
     * @JoinColumn(name="weapon_id", referencedColumnName="id")
     */
    private WeaponInterface $weapon;

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

    public function setModuleId(int $moduleId): WeaponShieldInterface
    {
        $this->module_id = $moduleId;

        return $this;
    }

    public function getWeaponId(): int
    {
        return $this->weapon_id;
    }

    public function setWeaponId(int $weaponid): WeaponShieldInterface
    {
        $this->weapon_id = $weaponid;

        return $this;
    }

    public function getModificator(): int
    {
        return $this->modificator;
    }

    public function setModificator(int $Modificator): WeaponShieldInterface
    {
        $this->modificator = $Modificator;

        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }

    public function setFactionId(int $factionid): WeaponShieldInterface
    {
        $this->faction_id = $factionid;

        return $this;
    }

    public function getWeapon(): WeaponInterface
    {
        return $this->weapon;
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
        $percent = 100 / 20 * ($this->getModificator() - 100);

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

    public function getFactionByModule($moduleid): ArrayCollection
    {
        $results = new ArrayCollection();

        for ($index = 1; $index <= 5; $index++) {
            $result = $this->findBy([
                'faction_id' => $index,
                'module_id' => $moduleid
            ]);

            if ($result) {
                $results->add($result);
            }
        }

        return $results;
    }
}