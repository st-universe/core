<?php

namespace Stu\Component\Queue\Message;

interface TransformableMessageInterface
{
    public function serialize(): array;

    public function unserialize(array $data): void;

    public function getId(): int;
}
