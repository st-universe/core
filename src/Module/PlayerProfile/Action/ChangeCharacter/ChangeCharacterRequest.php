<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\ChangeCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeCharacterRequest implements ChangeCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCharacterId(): int
    {
        return $this->parameter('character_id')->int()->required();
    }

    #[\Override]
    public function getName(): string
    {
        return $this->tidyString($this->parameter('name')->string()->required());
    }

    #[\Override]
    public function getDescription(): string
    {
        return $this->tidyString($this->parameter('description')->string()->required());
    }

    /**
     * @return array<string, mixed>
     */
    #[\Override]
    public function getAvatar(): array
    {
        return $_FILES['avatar'];
    }
}
