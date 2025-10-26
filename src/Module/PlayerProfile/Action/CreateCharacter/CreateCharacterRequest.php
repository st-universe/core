<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateCharacterRequest implements CreateCharacterRequestInterface
{
    use CustomControllerHelperTrait;

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
