<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditFullMapField;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use request;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Admin\View\Map\FullMapEditor\ShowFullMapEditorData;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;
use ValueError;

final class EditFullMapField implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_FULL_MAP_FIELD';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        private StarSystemTypeRepositoryInterface $starSystemTypeRepository,
        private MapRegionRepositoryInterface $mapRegionRepository,
        private MapBorderTypeRepositoryInterface $mapBorderTypeRepository,
        private StarSystemRepositoryInterface $starSystemRepository,
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

        $selectedFields = $this->getSelectedFields();
        if ($selectedFields === []) {
            $this->respondJson(['success' => false, 'message' => 'Kartenfeld nicht gefunden'], 404);
        }

        $operation = (string) request::postString('operation');
        $fieldTypeUpdates = [];
        $baseImageChanged = false;

        switch ($operation) {
            case 'fieldType':
                $fieldType = $this->mapFieldTypeRepository->find(request::postInt('value'));
                if ($fieldType === null || $fieldType->getIsSystem()) {
                    $this->respondJson(['success' => false, 'message' => 'Ungueltiger Feldtyp'], 400);
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setFieldType($fieldType);
                    $this->mapRepository->save($selectedField);
                }
                $baseImageChanged = true;
                break;

            case 'systemType':
                $systemTypeId = request::postInt('value');
                $systemType = null;
                if ($systemTypeId > 0) {
                    $systemType = $this->starSystemTypeRepository->find($systemTypeId);
                    if ($systemType === null) {
                        $this->respondJson(['success' => false, 'message' => 'Ungueltiger Systemtyp'], 400);
                    }
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setSystemTypeId($systemType?->getId());
                    $this->mapRepository->save($selectedField);
                }
                break;

            case 'region':
                $regionId = request::postInt('value');
                $region = null;
                if ($regionId > 0) {
                    $region = $this->mapRegionRepository->find($regionId);
                    if ($region === null) {
                        $this->respondJson(['success' => false, 'message' => 'Ungueltige Region'], 400);
                    }
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setRegionId($region?->getId());
                    $this->mapRepository->save($selectedField);
                }
                break;

            case 'adminRegion':
                $adminRegionId = request::postInt('value');
                $region = null;
                if ($adminRegionId > 0) {
                    $region = $this->mapRegionRepository->find($adminRegionId);
                    if ($region === null) {
                        $this->respondJson(['success' => false, 'message' => 'Ungueltige Admin-Region'], 400);
                    }
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setAdminRegionId($region?->getId());
                    $this->mapRepository->save($selectedField);
                }
                break;

            case 'influenceArea':
                $influenceAreaId = request::postInt('value');
                $system = null;
                if ($influenceAreaId !== 0 && $influenceAreaId !== 9999) {
                    $system = $this->starSystemRepository->find($influenceAreaId);
                    if ($system === null) {
                        $this->respondJson(['success' => false, 'message' => 'Ungueltiger Einflussbereich'], 400);
                    }
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setInfluenceArea($system);
                    $this->mapRepository->save($selectedField);
                }
                break;

            case 'passable':
                $passableValue = request::postInt('value');
                if ($passableValue !== 1 && $passableValue !== 2) {
                    $this->respondJson(['success' => false, 'message' => 'Ungueltige Passierbarkeit'], 400);
                }
                foreach ($selectedFields as $selectedField) {
                    $mapFieldType = $selectedField->getFieldType();
                    $fieldTypeId = $mapFieldType->getId();
                    if (isset($fieldTypeUpdates[$fieldTypeId])) {
                        continue;
                    }
                    $mapFieldType->setPassable($passableValue === 1);
                    $this->mapFieldTypeRepository->save($mapFieldType);
                    $fieldTypeUpdates[$fieldTypeId] = $this->normalizeFieldTypeUpdate($mapFieldType);
                }
                break;

            case 'border':
                $borderId = request::postInt('value');
                $border = null;
                if ($borderId > 0) {
                    $border = $this->mapBorderTypeRepository->find($borderId);
                    if ($border === null) {
                        $this->respondJson(['success' => false, 'message' => 'Ungueltige Border'], 400);
                    }
                }
                foreach ($selectedFields as $selectedField) {
                    $selectedField->setBorderTypeId($border?->getId());
                    $this->mapRepository->save($selectedField);
                }
                break;

            case 'effects':
                $effects = $this->parseEffects(request::postArray('effects'));
                $effectsMode = (string) request::postString('effectsMode');
                foreach ($selectedFields as $selectedField) {
                    $mapFieldType = $selectedField->getFieldType();
                    $fieldTypeId = $mapFieldType->getId();
                    if (isset($fieldTypeUpdates[$fieldTypeId])) {
                        continue;
                    }
                    $this->applyEffects($mapFieldType, $effects, $effectsMode);
                    $this->mapFieldTypeRepository->save($mapFieldType);
                    $fieldTypeUpdates[$fieldTypeId] = $this->normalizeFieldTypeUpdate($mapFieldType);
                }
                break;

            default:
                $this->respondJson(['success' => false, 'message' => 'Ungueltige Karteneditor-Aktion'], 400);
        }

        $this->entityManager->flush();
        $fields = [];
        foreach ($selectedFields as $selectedField) {
            $fieldRow = $this->mapRepository->getAdminFullMapEditorField($selectedField->getId());
            if ($fieldRow !== null) {
                $fields[] = ShowFullMapEditorData::normalizeFieldRow($fieldRow);
            }
        }
        $this->entityManager->commit();

        if ($fields === []) {
            $this->respondJson(['success' => false, 'message' => 'Kartenfelder konnten nicht neu geladen werden'], 500);
        }

        $payload = [
            'success' => true,
            'field' => $fields[0],
            'fields' => $fields,
            'changedFieldCount' => count($fields),
            'baseImageChanged' => $baseImageChanged
        ];

        if ($fieldTypeUpdates !== []) {
            $payload['fieldTypeUpdate'] = reset($fieldTypeUpdates);
            $payload['fieldTypeUpdates'] = array_values($fieldTypeUpdates);
        }

        $this->respondJson($payload);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }

    /**
     * @return array<int, Map>
     */
    private function getSelectedFields(): array
    {
        $fieldIds = [];
        foreach (request::postArray('fields') as $fieldId) {
            $fieldId = (int) $fieldId;
            if ($fieldId > 0) {
                $fieldIds[$fieldId] = $fieldId;
            }
        }

        $singleFieldId = request::postInt('field');
        if ($singleFieldId > 0) {
            $fieldIds[$singleFieldId] = $singleFieldId;
        }

        $fields = [];
        foreach ($fieldIds as $fieldId) {
            $field = $this->mapRepository->find($fieldId);
            if ($field instanceof Map) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * @param array<int|string, mixed> $effectValues
     * @return array<int, FieldTypeEffectEnum>
     */
    private function parseEffects(array $effectValues): array
    {
        $effects = [];
        foreach ($effectValues as $effectValue) {
            if (!is_string($effectValue)) {
                continue;
            }

            try {
                $effects[] = FieldTypeEffectEnum::from($effectValue);
            } catch (ValueError) {
                $this->respondJson(['success' => false, 'message' => 'Ungueltiger Effekt'], 400);
            }
        }

        return $effects;
    }

    /** @param array<int, FieldTypeEffectEnum> $selectedEffects */
    private function applyEffects(MapFieldType $mapFieldType, array $selectedEffects, string $mode): void
    {
        $currentEffects = [];
        foreach ($mapFieldType->getEffects() as $effect) {
            $currentEffects[$effect->value] = $effect;
        }

        $selectedEffectsByValue = [];
        foreach ($selectedEffects as $effect) {
            $selectedEffectsByValue[$effect->value] = $effect;
        }

        $result = match ($mode) {
            'replace' => $selectedEffectsByValue,
            'remove' => array_diff_key($currentEffects, $selectedEffectsByValue),
            default => $currentEffects + $selectedEffectsByValue
        };

        $mapFieldType->setEffects($result === [] ? null : array_values($result));
    }

    /** @return array{id: int, passable: bool, effects: array<int, string>} */
    private function normalizeFieldTypeUpdate(MapFieldType $mapFieldType): array
    {
        return [
            'id' => $mapFieldType->getId(),
            'passable' => $mapFieldType->getPassable(),
            'effects' => array_map(
                fn (FieldTypeEffectEnum $effect): string => $effect->value,
                $mapFieldType->getEffects()
            )
        ];
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
