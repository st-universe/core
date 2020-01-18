<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message\Type;

final class TerraformingJobProcessMessage implements TerraformingJobProcessMessageInterface
{
    private ?int $terraformingId;

    public function getId(): int
    {
        return MessageTypeEnum::TERRAFORMING_JOB;
    }

    public function getTerraformingId(): int {
        return $this->terraformingId;
    }

    public function setTerraformingId(int $terraformingId): TerraformingJobProcessMessageInterface {
        $this->terraformingId = $terraformingId;

        return $this;
    }

    public function serialize(): array
    {
        return [
            'terraformingId' => $this->terraformingId,
        ];
    }

    public function unserialize(array $data): void
    {
        $this->terraformingId = $data['terraformingId'];
    }
}
