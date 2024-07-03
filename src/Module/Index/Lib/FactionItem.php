<?php

declare(strict_types=1);

namespace Stu\Module\Index\Lib;

use Stu\Orm\Entity\FactionInterface;

/**
 * Wrap the faction and provide additional methods - for use in TAL
 */
class FactionItem
{
    public function __construct(private FactionInterface $faction, private int $currentPlayerCount)
    {
    }

    /**
     * Returns the count of players of this faction
     */
    public function getPlayerCount(): int
    {
        return $this->currentPlayerCount;
    }

    /**
     * Returns `true` if the faction has free player slots
     */
    public function hasFreePlayerSlots(): bool
    {
        $playerLimit = $this->faction->getPlayerLimit();

        return $playerLimit === 0
            || $this->currentPlayerCount < $playerLimit;
    }

    /**
     * Returns the color for ui purposes
     */
    public function getColor(): string
    {
        return $this->faction->getDarkerColor();
    }

    /**
     * Returns the faction id
     */
    public function getId(): int
    {
        return $this->faction->getId();
    }

    /**
     * Returns the faction name
     */
    public function getName(): string
    {
        return $this->faction->getName();
    }

    /**
     * Returns the faction's player limit
     */
    public function getPlayerLimit(): int
    {
        return $this->faction->getPlayerLimit();
    }

    /**
     * Returns the faction's description
     */
    public function getDescription(): string
    {
        return $this->faction->getDescription();
    }
}
