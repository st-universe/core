<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Lib;

use JsonSerializable;

final class RumpCreatorData implements JsonSerializable
{
    public function __construct(private mixed $data) {}

    public function get(int|string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function all(): mixed
    {
        return $this->data;
    }

    public function without(string ...$keys): mixed
    {
        $data = $this->data;
        foreach ($keys as $key) {
            unset($data[$key]);
        }

        return $data;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->data;
    }
}
