<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Stu\Component\Building\NameAbbreviations;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Module\Colony\Lib\ColonyEpsProductionPreviewWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyProductionPreviewWrapper;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Tal\TalHelper;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ShipInterface;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigHelper
{
    public function __construct(private Environment $environment, private Parser $parser, private ConfigInterface $config, private FightLibInterface $fightLib, private ColonyLibFactoryInterface $colonyLibFactory, private ShipCrewCalculatorInterface $shipCrewCalculator, private GradientColorInterface $gradientColor)
    {
    }

    public function registerGlobalVariables(): void
    {
        $this->environment->addGlobal(
            'ASSET_PATHS',
            [
                'alliance' => $this->config->get('game.alliance_avatar_path'),
                'user' => $this->config->get('game.user_avatar_path'),
                'faction' => 'assets/rassen/',
            ]
        );
    }

    /**
     * Registers global available twig methods and filters
     */
    public function registerFiltersAndFunctions(): void
    {
        $this->registerFilters();
        $this->registerFunctions();
    }

    private function registerFilters(): void
    {
        $bbcode2txtFilter = new TwigFilter('bbcode2txt', function ($string): string {
            return $this->parser->parse($string)->getAsText();
        });
        $this->environment->addFilter($bbcode2txtFilter);

        $bbcodeFilter = new TwigFilter('bbcode', function ($string): string {
            return $this->parser->parse($string)->getAsHTML();
        }, ['is_safe' => ['html']]);
        $this->environment->addFilter($bbcodeFilter);

        $jsquoteFilter = new TwigFilter('jsquote', function ($string): string {
            return TalHelper::jsquote($string);
        });
        $this->environment->addFilter($jsquoteFilter);

        $addPlusCharacterFilter = new TwigFilter('addPlusCharacter', function ($value): string {
            if (is_int($value)) {
                return TalHelper::addPlusCharacter((string) $value);
            }
            return TalHelper::addPlusCharacter($value);
        });
        $this->environment->addFilter($addPlusCharacterFilter);

        $formatSecondsFilter = new TwigFilter('formatSeconds', function ($value): string {
            if (is_int($value)) {
                return TalHelper::formatSeconds((string) $value);
            }
            return TalHelper::formatSeconds($value);
        });
        $this->environment->addFilter($formatSecondsFilter);

        $planetFieldTitleFilter = new TwigFilter('planetFieldTitle', function ($planetField): string {
            return TalHelper::getPlanetFieldTitle($planetField);
        });
        $this->environment->addFilter($planetFieldTitleFilter);

        $planetFieldTypeDescriptionFilter = new TwigFilter('planetFieldTypeDescription', function ($id): string {
            return TalHelper::getPlanetFieldTypeDescription($id);
        });
        $this->environment->addFilter($planetFieldTypeDescriptionFilter);

        $formatProductionValueFilter = new TwigFilter('formatProductionValue', function ($value): string {
            return TalHelper::formatProductionValue($value);
        });
        $this->environment->addFilter($formatProductionValueFilter);

        $isPositiveFilter = new TwigFilter('isPositive', function (int $value): bool {
            return $value > 0;
        });
        $this->environment->addFilter($isPositiveFilter);

        $stuDateTimeFilter = new TwigFilter('stuDateTime', function ($value): string {
            return sprintf(
                '%s%s %s',
                date('d.m.', $value),
                (int)date("Y", $value) + StuTime::STU_YEARS_IN_FUTURE_OFFSET,
                date("H:i", $value)
            );
        });
        $this->environment->addFilter($stuDateTimeFilter);

        $stuDateFilter = new TwigFilter('stuDate', function ($value): string {
            return sprintf(
                '%s%s',
                date('d.m.', $value),
                (int)date("Y", $value) + StuTime::STU_YEARS_IN_FUTURE_OFFSET
            );
        });
        $this->environment->addFilter($stuDateFilter);

        $nl2brFilter = new TwigFilter('nl2br', function (string $value): string {
            return nl2br($value);
        });
        $this->environment->addFilter($nl2brFilter);

        $htmlSafeFilter = new TwigFilter('htmlSafe', function (string $text): string {
            return htmlspecialchars($text);
        });
        $this->environment->addFilter($htmlSafeFilter);

        $adventDoorFilter = new TwigFilter('adventDoor', function (AnomalyInterface $anomaly): int {
            return (int)((120 - $anomaly->getRemainingTicks()) / 5) + 1;
        });
        $this->environment->addFilter($adventDoorFilter);

        $shortNameFilter = new TwigFilter('shortName', function (string $name): string {
            return array_reduce(
                array_keys(NameAbbreviations::ABBREVIATIONS),
                fn (string $value, string $from): string => str_replace($from, NameAbbreviations::ABBREVIATIONS[$from], $value),
                $name
            );
        });
        $this->environment->addFilter($shortNameFilter);

        $getMaxCrewCountByShipFilter = new TwigFilter('getMaxCrewCountByShip', function (ShipInterface $ship): int {
            return $this->shipCrewCalculator->getMaxCrewCountByShip($ship);
        });
        $this->environment->addFilter($getMaxCrewCountByShipFilter);

        $numberWithThousandSeperatorFilter = new TwigFilter('numberWithThousandSeperator', function ($value): string {
            return TalHelper::getNumberWithThousandSeperator($value);
        });
        $this->environment->addFilter($numberWithThousandSeperatorFilter);
    }

    private function registerFunctions(): void
    {
        $canAttackTargetFunction = new TwigFunction('canAttackTarget', function (ShipInterface $ship, ShipInterface|ShipNfsItem $target): bool {
            return $this->fightLib->canAttackTarget($ship, $target);
        });
        $this->environment->addFunction($canAttackTargetFunction);

        $getEpsProductionPreviewFunction = new TwigFunction('getEpsProductionPreview', function (PlanetFieldHostInterface $host, BuildingInterface $building): ColonyEpsProductionPreviewWrapper {
            return $this->colonyLibFactory->createEpsProductionPreviewWrapper($host, $building);
        });
        $this->environment->addFunction($getEpsProductionPreviewFunction);

        $getCommodityProductionPreviewFunction = new TwigFunction('getCommodityProductionPreview', function (PlanetFieldHostInterface $host, BuildingInterface $building): ColonyProductionPreviewWrapper {
            return $this->colonyLibFactory->createColonyProductionPreviewWrapper($building, $host);
        });
        $this->environment->addFunction($getCommodityProductionPreviewFunction);

        $getColonyMenuClassFunction = new TwigFunction('getColonyMenuClass', function (ColonyMenuEnum $currentMenu, int $value): string {
            return ColonyMenuEnum::getMenuClass($currentMenu, $value);
        });
        $this->environment->addFunction($getColonyMenuClassFunction);

        $getViewFunction = new TwigFunction('getView', function (string $value): ModuleViewEnum {
            return ModuleViewEnum::from($value);
        });
        $this->environment->addFunction($getViewFunction);

        $getUniqIdFunction = new TwigFunction('getUniqId', function (): string {
            return uniqid();
        });
        $this->environment->addFunction($getUniqIdFunction);

        $gradientColorFunction = new TwigFunction('gradientColor', function (int $value, int $lowest, int $highest): string {
            return $this->gradientColor->calculateGradientColor($value, $lowest, $highest);
        });
        $this->environment->addFunction($gradientColorFunction);
    }
}
