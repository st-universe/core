<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\ChangeCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class ChangeCharacterRequest implements ChangeCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCharacterId(): int
    {
        return $this->queryParameter('character_id')->int()->required();
    }

    public function getName(): string
    {
        return $this->tidyString($this->queryParameter('name')->string()->required());
    }

    public function getDescription(): string
    {
        return $this->tidyString($this->queryParameter('description')->string()->required());
    }

    /**
     * @return array<string, mixed>
     */
    public function getAvatar(): array
    {
        return $_FILES['avatar'];
    }
}
