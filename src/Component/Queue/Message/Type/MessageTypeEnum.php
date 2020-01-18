<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message\Type;

use Stu\Component\Queue\Message\TransformableMessageInterface;

abstract class MessageTypeEnum
{
    public const BUILDING_JOB = 1;
    public const TERRAFORMING_JOB = 2;

    public static function map(int $typeId): TransformableMessageInterface
    {
        $map = [
            static::BUILDING_JOB => function (): BuildingJobProcessMessageInterface {
                return new BuildingJobProcessMessage();
            },
            static::TERRAFORMING_JOB => function (): TerraformingJobProcessMessageInterface {
                return new TerraformingJobProcessMessage();
            },
        ];

        $type = $map[$typeId] ?? null;

        if ($type === null) {
            // @todo add exception
            throw new \InvalidArgumentException();
        }

        return $type();
    }
}
