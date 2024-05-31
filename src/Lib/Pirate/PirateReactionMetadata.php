<?php

namespace Stu\Lib\Pirate;

class PirateReactionMetadata
{
    /** @var array<int, int> $reactions */
    private array $reactions = [];

    public function addReaction(PirateBehaviourEnum $reaction): void
    {
        $key = $reaction->value;

        if (!array_key_exists($key, $this->reactions)) {
            $this->reactions[$key] = 1;
        } else {
            $this->reactions[$key]++;
        }
    }

    public function getReactionAmount(PirateBehaviourEnum $reaction): int
    {
        $key = $reaction->value;

        if (!array_key_exists($key, $this->reactions)) {
            return 0;
        }

        return $this->reactions[$key];
    }
}
