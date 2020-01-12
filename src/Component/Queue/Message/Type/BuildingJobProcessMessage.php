<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message\Type;

final class BuildingJobProcessMessage implements BuildingJobProcessMessageInterface
{
    private ?int $planetFieldId;

    public function getId(): int
    {
        return MessageTypeEnum::BUILDING_JOB;
    }

    public function getPlanetFieldId(): int
    {
        return $this->planetFieldId;
    }

    public function setPlanetFieldId(int $planetFieldId): BuildingJobProcessMessageInterface
    {
        $this->planetFieldId = $planetFieldId;

        return $this;
    }

    public function serialize(): array
    {
        return [
            'planetFieldId' => $this->planetFieldId
        ];
    }

    public function unserialize(array $data): void
    {
        $this->planetFieldId = $data['planetFieldId'];
    }
}
