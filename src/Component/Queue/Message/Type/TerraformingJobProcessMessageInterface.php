<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message\Type;

use Stu\Component\Queue\Message\TransformableMessageInterface;

interface TerraformingJobProcessMessageInterface extends TransformableMessageInterface
{
    public function getId(): int;

    public function getTerraformingId(): int;

    public function setTerraformingId(int $terraformingId): TerraformingJobProcessMessageInterface;

    public function serialize(): array;

    public function unserialize(array $data): void;
}
