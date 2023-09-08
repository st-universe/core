<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use RuntimeException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\LayerInterface;

class EncodedMap implements EncodedMapInterface
{
    private StuConfigInterface $stuConfig;

    public function __construct(StuConfigInterface $stuConfig)
    {
        $this->stuConfig = $stuConfig;
    }

    public function getEncodedMapPath(int $mapFieldType, LayerInterface $layer): string
    {
        $key = $this->stuConfig->getGameSettings()->getMapSettings()->getEncryptionKey();
        if ($key === null) {
            throw new RuntimeException('encoding key is missing in configuration');
        }

        $cipher = base64_encode(crypt((string)$mapFieldType, $key));
        $cipher = str_replace('/', '5', $cipher);

        return sprintf(
            '%d/encoded/%s.png',
            $layer->getId(),
            implode("/", str_split($cipher, 8))
        );
    }
}
