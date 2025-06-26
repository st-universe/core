<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Override;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\Layer;

class EncodedMap implements EncodedMapInterface
{
    private ?string $key = null;

    public function __construct(private StuConfigInterface $stuConfig) {}

    #[Override]
    public function getEncodedMapPath(int $mapFieldType, Layer $layer): string
    {
        $key = $this->getKey();

        return sprintf(
            '%d/encoded/%s.png',
            $layer->getId(),
            implode("/", str_split(bin2hex(base64_encode(crypt((string)$mapFieldType, $key))), 8))
        );
    }

    private function getKey(): string
    {
        if ($this->key === null) {
            $key = $this->stuConfig->getGameSettings()->getMapSettings()->getEncryptionKey();

            if ($key === null) {
                throw new RuntimeException('encoding key is missing in configuration');
            }

            $this->key = $key;
        }

        return $this->key;
    }
}
