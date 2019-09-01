<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use User;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FactionRepository")
 * @Table(
 *     name="stu_factions"
 * )
 **/
class Faction implements FactionInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") * */
    private $name = '';

    /** @Column(type="text") * */
    private $description = '';

    /** @Column(type="string") * */
    private $darker_color = '';

    /** @Column(type="boolean") * */
    private $chooseable = false;

    /** @Column(type="integer") * */
    private $player_limit = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): FactionInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): FactionInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDarkerColor(): string
    {
        return $this->darker_color;
    }

    public function setDarkerColor(string $darkerColor): FactionInterface
    {
        $this->darker_color = $darkerColor;

        return $this;
    }

    public function getChooseable(): bool
    {
        return $this->chooseable;
    }

    public function setChooseable(bool $chooseable): FactionInterface
    {
        $this->chooseable = $chooseable;

        return $this;
    }

    public function getPlayerLimit(): int
    {
        return $this->player_limit;
    }

    public function setPlayerLimit(int $playerLimit): FactionInterface
    {
        $this->player_limit = $playerLimit;

        return $this;
    }

	public function getPlayerAmount(): int {
        // @todo refactor
        return User::getAmountByFaction($this->getId());
	}

	public function hasFreePlayerSlots(): bool {
		return $this->getPlayerLimit() === 0 || $this->getPlayerAmount() < $this->getPlayerLimit();
	}
}
