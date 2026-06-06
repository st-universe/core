<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\SetIonStormMovement;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use request;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Component\Anomaly\Type\IonStorm\IonStormMovementType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Repository\AnomalyRepositoryInterface;

final class SetIonStormMovement implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_ION_STORM_MOVEMENT';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;
    private const int MAX_VELOCITY = 5;

    public function __construct(
        private AnomalyRepositoryInterface $anomalyRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws JsonException
     */
    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $this->respondJson(['success' => false, 'message' => 'Aktion nur fuer Admins moeglich'], 403);
        }

        $rootId = request::postInt('rootId');
        $directionInDegrees = $this->parseIntPostValue('directionInDegrees');
        $velocity = $this->parseIntPostValue('velocity');
        $movementType = $this->parseIntPostValue('movementType');

        if ($rootId <= 0 || $directionInDegrees === null || $velocity === null || $movementType === null) {
            $this->respondJson(['success' => false, 'message' => 'Ungueltige Ionensturm-Daten'], 400);
        }

        $root = $this->getIonStormRoot($rootId);
        if ($root === null) {
            $this->respondJson(['success' => false, 'message' => 'Ionensturm nicht gefunden'], 404);
        }

        $movement = $this->normalizeMovement(
            $directionInDegrees,
            $velocity,
            $movementType,
            $root->getData()
        );

        $root->setData(json_encode($movement['data'], self::JSON_FLAGS));
        $this->anomalyRepository->save($root);
        $this->entityManager->flush();
        $this->entityManager->commit();

        $this->respondJson([
            'success' => true,
            'rootId' => $root->getId(),
            'movement' => $movement['response']
        ]);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }

    private function parseIntPostValue(string $key): ?int
    {
        $value = request::postString($key);
        if ($value === false || trim($value) === '' || !is_numeric($value)) {
            return null;
        }

        return (int) round((float) $value);
    }

    private function getIonStormRoot(int $rootId): ?Anomaly
    {
        $anomaly = $this->anomalyRepository->find($rootId);
        if ($anomaly === null) {
            return null;
        }

        $root = $anomaly->getRoot();
        if (
            !$root->isActive()
            || $root->getAnomalyType()->getId() !== AnomalyTypeEnum::ION_STORM->value
        ) {
            return null;
        }

        return $root;
    }

    /**
     * @return array{
     *     data: array{
     *         directionInDegrees: int,
     *         velocity: int,
     *         movementType: int,
     *         isPositivePolarity: bool,
     *         intensity: int
     *     },
     *     response: array{
     *         directionInDegrees: int,
     *         velocity: int,
     *         movementType: int,
     *         isVariable: bool,
     *         dx: int,
     *         dy: int
     *     }
     * }
     */
    private function normalizeMovement(
        int $directionInDegrees,
        int $velocity,
        int $movementType,
        ?string $currentData
    ): array {
        $directionInDegrees %= 360;
        if ($directionInDegrees < 0) {
            $directionInDegrees += 360;
        }

        $velocity = max(0, min(self::MAX_VELOCITY, $velocity));
        if (
            $movementType !== IonStormMovementType::STATIC->value
            && $movementType !== IonStormMovementType::VARIABLE->value
        ) {
            $movementType = IonStormMovementType::STATIC->value;
        }

        $decodedData = $this->decodeCurrentData($currentData);
        $isVariable = $movementType === IonStormMovementType::VARIABLE->value;
        $data = [
            'directionInDegrees' => $directionInDegrees,
            'velocity' => $velocity,
            'movementType' => $movementType,
            'isPositivePolarity' => (bool) ($decodedData['isPositivePolarity'] ?? true),
            'intensity' => max(0, (int) ($decodedData['intensity'] ?? 100))
        ];

        return [
            'data' => $data,
            'response' => [
                'directionInDegrees' => $directionInDegrees,
                'velocity' => $velocity,
                'movementType' => $movementType,
                'isVariable' => $isVariable,
                'dx' => (int) round(sin(deg2rad($directionInDegrees)) * $velocity),
                'dy' => (int) round(cos(deg2rad($directionInDegrees)) * $velocity),
            ]
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function decodeCurrentData(?string $currentData): array
    {
        if ($currentData === null || $currentData === '') {
            return [];
        }

        $decoded = json_decode($currentData, true);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param array<string, mixed> $payload
     * @throws JsonException
     */
    private function respondJson(array $payload, int $statusCode = 200): never
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, self::JSON_FLAGS);
        exit;
    }
}
