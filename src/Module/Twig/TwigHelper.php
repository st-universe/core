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
        $bbcode2txtFilter = new TwigFilter('bbcode2txt', fn($string): string => $this->parser->parse($string)->getAsText());
        $this->environment->addFilter($bbcode2txtFilter);

        $bbcodeFilter = new TwigFilter('bbcode', fn($string): string => $this->parser->parse($string)->getAsHTML(), ['is_safe' => ['html']]);
        $this->environment->addFilter($bbcodeFilter);

        $jsquoteFilter = new TwigFilter('jsquote', fn($string): string => TalHelper::jsquote($string));
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

        $planetFieldTitleFilter = new TwigFilter('planetFieldTitle', fn($planetField): string => TalHelper::getPlanetFieldTitle($planetField));
        $this->environment->addFilter($planetFieldTitleFilter);

        $planetFieldTypeDescriptionFilter = new TwigFilter('planetFieldTypeDescription', fn($id): string => TalHelper::getPlanetFieldTypeDescription($id));
        $this->environment->addFilter($planetFieldTypeDescriptionFilter);

        $formatProductionValueFilter = new TwigFilter('formatProductionValue', fn($value): string => TalHelper::formatProductionValue($value));
        $this->environment->addFilter($formatProductionValueFilter);

        $isPositiveFilter = new TwigFilter('isPositive', fn(int $value): bool => $value > 0);
        $this->environment->addFilter($isPositiveFilter);

        $stuDateTimeFilter = new TwigFilter('stuDateTime', fn($value): string => sprintf(
            '%s%s %s',
            date('d.m.', $value),
            (int)date("Y", $value) + StuTime::STU_YEARS_IN_FUTURE_OFFSET,
            date("H:i", $value)
        ));
        $this->environment->addFilter($stuDateTimeFilter);

        $stuDateFilter = new TwigFilter('stuDate', fn($value): string => sprintf(
            '%s%s',
            date('d.m.', $value),
            (int)date("Y", $value) + StuTime::STU_YEARS_IN_FUTURE_OFFSET
        ));
        $this->environment->addFilter($stuDateFilter);

        $nl2brFilter = new TwigFilter('nl2br', fn(string $value): string => nl2br($value));
        $this->environment->addFilter($nl2brFilter);

        $htmlSafeFilter = new TwigFilter('htmlSafe', fn(string $text): string => htmlspecialchars($text));
        $this->environment->addFilter($htmlSafeFilter);

        $adventDoorFilter = new TwigFilter('adventDoor', fn(AnomalyInterface $anomaly): int => (int)((120 - $anomaly->getRemainingTicks()) / 5) + 1);
        $this->environment->addFilter($adventDoorFilter);

        $shortNameFilter = new TwigFilter('shortName', fn(string $name): string => array_reduce(
            array_keys(NameAbbreviations::ABBREVIATIONS),
            fn (string $value, string $from): string => str_replace($from, NameAbbreviations::ABBREVIATIONS[$from], $value),
            $name
        ));
        $this->environment->addFilter($shortNameFilter);

        $getMaxCrewCountByShipFilter = new TwigFilter('getMaxCrewCountByShip', fn(ShipInterface $ship): int => $this->shipCrewCalculator->getMaxCrewCountByShip($ship));
        $this->environment->addFilter($getMaxCrewCountByShipFilter);

        $numberWithThousandSeperatorFilter = new TwigFilter('numberWithThousandSeperator', fn($value): string => TalHelper::getNumberWithThousandSeperator($value));
        $this->environment->addFilter($numberWithThousandSeperatorFilter);
    }

    private function registerFunctions(): void
    {
        $canAttackTargetFunction = new TwigFunction('canAttackTarget', fn(ShipInterface $ship, ShipInterface|ShipNfsItem $target): bool => $this->fightLib->canAttackTarget($ship, $target));
        $this->environment->addFunction($canAttackTargetFunction);

        $getEpsProductionPreviewFunction = new TwigFunction('getEpsProductionPreview', fn(PlanetFieldHostInterface $host, BuildingInterface $building): ColonyEpsProductionPreviewWrapper => $this->colonyLibFactory->createEpsProductionPreviewWrapper($host, $building));
        $this->environment->addFunction($getEpsProductionPreviewFunction);

        $getCommodityProductionPreviewFunction = new TwigFunction('getCommodityProductionPreview', fn(PlanetFieldHostInterface $host, BuildingInterface $building): ColonyProductionPreviewWrapper => $this->colonyLibFactory->createColonyProductionPreviewWrapper($building, $host));
        $this->environment->addFunction($getCommodityProductionPreviewFunction);

        $getColonyMenuClassFunction = new TwigFunction('getColonyMenuClass', fn(ColonyMenuEnum $currentMenu, int $value): string => ColonyMenuEnum::getMenuClass($currentMenu, $value));
        $this->environment->addFunction($getColonyMenuClassFunction);

        $getViewFunction = new TwigFunction('getView', fn(string $value): ModuleViewEnum => ModuleViewEnum::from($value));
        $this->environment->addFunction($getViewFunction);

        $getUniqIdFunction = new TwigFunction('getUniqId', fn(): string => uniqid());
        $this->environment->addFunction($getUniqIdFunction);

        $gradientColorFunction = new TwigFunction('gradientColor', fn(int $value, int $lowest, int $highest): string => $this->gradientColor->calculateGradientColor($value, $lowest, $highest));
        $this->environment->addFunction($gradientColorFunction);
    }
}
