<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Action\CreateCharacter;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateCharacterRequest implements CreateCharacterRequestInterface
{
    use CustomControllerHelperTrait;

    public function getName(): string
    {
        return $this->tidyString($this->queryParameter('name')->string()->required());
    }

    public function getDescription(): string
    {
        return $this->tidyString($this->queryParameter('description')->string()->required());
    }

    public function getAvatar(): array
    {
        return $_FILES['avatar'];
    }
}
