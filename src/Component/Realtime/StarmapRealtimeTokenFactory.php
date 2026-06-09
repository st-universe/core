<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use JsonException;
use Stu\Module\Config\StuConfigInterface;

final class StarmapRealtimeTokenFactory
{
    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    public function __construct(private StuConfigInterface $config) {}

    /**
     * @throws JsonException
     */
    /**
     * @param array<string, mixed> $claims
     */
    public function create(int $userId, int $layerId, int $ttlSeconds = 300, array $claims = []): string
    {
        $payload = [
            'userId' => $userId,
            'layerId' => $layerId,
            'exp' => time() + $ttlSeconds,
            'nonce' => bin2hex(random_bytes(8))
        ] + $claims;

        $payloadEncoded = $this->base64UrlEncode(json_encode($payload, self::JSON_FLAGS));
        $signature = hash_hmac('sha256', $payloadEncoded, $this->getSecret(), true);

        return sprintf('%s.%s', $payloadEncoded, $this->base64UrlEncode($signature));
    }

    private function getSecret(): string
    {
        return $this->config->getGameSettings()->getMapSettings()->getEncryptionKey()
            ?? $this->config->getDbSettings()->getDatabase();
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
