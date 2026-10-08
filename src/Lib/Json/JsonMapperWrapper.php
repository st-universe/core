<?php

declare(strict_types=1);

namespace Stu\Lib\Json;

use JsonMapper;

final class JsonMapperWrapper implements JsonMapperInterface
{
    private JsonMapper $wrapped;

    public function __construct() {
        $this->wrapped = new JsonMapper();
    }

    /**
     * @template T of object
     *
     * @param T $object
     *
     * @return T
     */
    public function mapObjectFromString(string $json, $object)
    {
        /** @return T $result */
        return $this->wrapped->map(json_decode($json), $object);
    }
}
