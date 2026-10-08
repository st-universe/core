<?php

declare(strict_types=1);

namespace Stu\Lib\Json;

interface JsonMapperInterface
{
    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function mapObjectFromString(string $json, $object);
}
