<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\GameConfigRepository")
 * @Table(
 *     name="stu_game_config",
 *     indexes={
 *         @Index(name="option_idx", columns={"option"})
 *     }
 * )
 **/
class GameConfig implements GameConfigInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="smallint") * */
    private $option = 0;

    /** @Column(type="smallint") * */
    private $value = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getOption(): int
    {
        return $this->option;
    }

    public function setOption(int $option): GameConfigInterface
    {
        $this->option = $option;

        return $this;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): GameConfigInterface
    {
        $this->value = $value;

        return $this;
    }
}
