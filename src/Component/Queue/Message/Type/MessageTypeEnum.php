<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Message\Type;

use Stu\Component\Queue\Message\TransformableMessageInterface;

abstract class MessageTypeEnum
{
    public const BUILDING_JOB = 1;

    public static function map(int $typeId): TransformableMessageInterface
    {
        $map = [
            static::BUILDING_JOB => function (): BuildingJobProcessMessageInterface {
                return new BuildingJobProcessMessage();
            },
        ];

        $type = $map[$typeId] ?? null;

        if ($type === null) {
            // @todo add exception
            throw new \InvalidParamException();
        }

        return $type();
    }
}
