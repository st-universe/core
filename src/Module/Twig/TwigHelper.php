<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Building\NameAbbreviations;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Component\Player\UserRpgBehaviorEnum;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapper;
use Stu\Component\Spacecraft\System\SpacecraftSystemWrapperFactoryInterface;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Colony\Lib\ColonyEpsProductionPreviewWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyProductionPreviewWrapper;
use Stu\Module\Control\AccessCheckInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\UserContainer;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftNfsItem;
use Stu\Module\Template\TemplateHelperInterface;
use Stu\Orm\Entity\Anomaly;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigHelper
{
    public function __construct(
        private readonly GameControllerInterface $game,
        private readonly Environment $environment,
        private readonly Parser $parser,
        private readonly ConfigInterface $config,
        private readonly FightLibInterface $fightLib,
        private readonly ColonyLibFactoryInterface $colonyLibFactory,
        private readonly SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private readonly SpacecraftSystemWrapperFactoryInterface $spacecraftSystemWrapperFactory,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly GradientColorInterface $gradientColor,
        private readonly TemplateHelperInterface $templateHelper,
        private readonly AccessCheckInterface $accessCheck,
        private readonly StuTime $stuTime,
        private readonly StuRandom $stuRandom,
        private readonly AllianceJobManagerInterface $allianceJobManager
    ) {}

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

        $jsquoteFilter = new TwigFilter('jsquote', fn($string): string => $this->templateHelper->jsquote($string));
        $this->environment->addFilter($jsquoteFilter);

        $addPlusCharacterFilter = new TwigFilter('addPlusCharacter', function ($value): string {
            if (is_int($value)) {
                return $this->templateHelper->addPlusCharacter((string) $value);
            }
            return $this->templateHelper->addPlusCharacter($value);
        });
        $this->environment->addFilter($addPlusCharacterFilter);

        $formatSecondsFilter = new TwigFilter('formatSeconds', function ($value): string {
            if (is_int($value)) {
                return $this->templateHelper->formatSeconds((string) $value);
            }
            return $this->templateHelper->formatSeconds($value);
        });
        $this->environment->addFilter($formatSecondsFilter);

        $planetFieldTitleFilter = new TwigFilter('planetFieldTitle', fn($planetField): string => $this->templateHelper->getPlanetFieldTitle($planetField));
        $this->environment->addFilter($planetFieldTitleFilter);

        $planetFieldTypeDescriptionFilter = new TwigFilter('planetFieldTypeDescription', fn($id): string => $this->templateHelper->getPlanetFieldTypeDescription($id));
        $this->environment->addFilter($planetFieldTypeDescriptionFilter);

        $formatProductionValueFilter = new TwigFilter('formatProductionValue', fn($value): string => $this->templateHelper->formatProductionValue($value));
        $this->environment->addFilter($formatProductionValueFilter);

        $isPositiveFilter = new TwigFilter('isPositive', fn(int $value): bool => $value > 0);
        $this->environment->addFilter($isPositiveFilter);

        $stuDateTimeFilter = new TwigFilter('stuDateTime', fn($value): string => $this->stuTime->transformToStuDateTime($value));
        $this->environment->addFilter($stuDateTimeFilter);

        $stuDateFilter = new TwigFilter('stuDate', fn($value): string => $this->stuTime->transformToStuDate($value));
        $this->environment->addFilter($stuDateFilter);

        $nl2brFilter = new TwigFilter('nl2br', fn(string $value): string => nl2br($value));
        $this->environment->addFilter($nl2brFilter);

        $htmlSafeFilter = new TwigFilter('htmlSafe', fn(string $text): string => htmlspecialchars($text));
        $this->environment->addFilter($htmlSafeFilter);

        $adventDoorFilter = new TwigFilter('adventDoor', fn(Anomaly $anomaly): int => (int)((120 - $anomaly->getRemainingTicks()) / 5) + 1);
        $this->environment->addFilter($adventDoorFilter);

        $shortNameFilter = new TwigFilter('shortName', fn(string $name): string => array_reduce(
            array_keys(NameAbbreviations::ABBREVIATIONS),
            fn(string $value, string $from): string => str_replace($from, NameAbbreviations::ABBREVIATIONS[$from], $value),
            $name
        ));
        $this->environment->addFilter($shortNameFilter);

        $getMaxCrewCountByShipFilter = new TwigFilter('getMaxCrewCountByShip', fn(Spacecraft $spacecraft): int => $this->shipCrewCalculator->getMaxCrewCountByShip($spacecraft));
        $this->environment->addFilter($getMaxCrewCountByShipFilter);

        $numberWithThousandSeperatorFilter = new TwigFilter('numberWithThousandSeperator', fn($value): string => $this->templateHelper->getNumberWithThousandSeperator($value));
        $this->environment->addFilter($numberWithThousandSeperatorFilter);
    }

    private function registerFunctions(): void
    {
        $canAttackTargetFunction = new TwigFunction('canAttackTarget', fn(Spacecraft $spacecraft, Spacecraft|SpacecraftNfsItem $target): bool => $this->fightLib->canAttackTarget($spacecraft, $target));
        $this->environment->addFunction($canAttackTargetFunction);

        $getEpsProductionPreviewFunction = new TwigFunction('getEpsProductionPreview', fn(PlanetFieldHostInterface $host, Building $building): ColonyEpsProductionPreviewWrapper => $this->colonyLibFactory->createEpsProductionPreviewWrapper($host, $building));
        $this->environment->addFunction($getEpsProductionPreviewFunction);

        $getCommodityProductionPreviewFunction = new TwigFunction('getCommodityProductionPreview', fn(PlanetFieldHostInterface $host, Building $building): ColonyProductionPreviewWrapper => $this->colonyLibFactory->createColonyProductionPreviewWrapper($building, $host));
        $this->environment->addFunction($getCommodityProductionPreviewFunction);

        $getColonyMenuClassFunction = new TwigFunction('getColonyMenuClass', fn(ColonyMenuEnum $currentMenu, int $value): string => ColonyMenuEnum::getMenuClass($currentMenu, $value));
        $this->environment->addFunction($getColonyMenuClassFunction);

        $getViewFunction = new TwigFunction('getView', fn(string $value): ModuleEnum => ModuleEnum::from($value));
        $this->environment->addFunction($getViewFunction);

        $getUniqIdFunction = new TwigFunction('getUniqId', fn(): string => $this->stuRandom->uniqid());
        $this->environment->addFunction($getUniqIdFunction);

        $gradientColorFunction = new TwigFunction('gradientColor', fn(int $value, int $lowest, int $highest): string => $this->gradientColor->calculateGradientColor($value, $lowest, $highest));
        $this->environment->addFunction($gradientColorFunction);

        $gradientColorOverLimitFunction = new TwigFunction('gradientColorOverLimit', fn(int $value, int $lowest, int $highest): string => $this->gradientColor->calculateGradientColor(min($value, $highest), $lowest, $highest));
        $this->environment->addFunction($gradientColorOverLimitFunction);

        $stuDateFunction = new TwigFunction('stuDate', fn(string $format): string => $this->stuTime->date($format));
        $this->environment->addFunction($stuDateFunction);

        $dayNightPrefixFunction = new TwigFunction('getDayNightPrefix', fn(PlanetField $field): string => $field->getDayNightPrefix($this->stuTime->time()));
        $this->environment->addFunction($dayNightPrefixFunction);

        $maskEmailFunction = new TwigFunction('maskEmail', fn(string $email): string => $this->maskEmail($email));
        $this->environment->addFunction($maskEmailFunction);

        $maskMobileFunction = new TwigFunction('maskMobile', fn(?string $mobile): string => $this->maskMobile($mobile));
        $this->environment->addFunction($maskMobileFunction);

        $getSpacecraftSystemWrapperFunction = new TwigFunction(
            'getSpacecraftSystemWrapper',
            fn(Spacecraft $spacecraft, string $name): ?SpacecraftSystemWrapper
            => $this->spacecraftSystemWrapperFactory->create($spacecraft, SpacecraftSystemTypeEnum::getByName($name))
        );
        $this->environment->addFunction($getSpacecraftSystemWrapperFunction);

        $isFeatureGrantedFunction = new TwigFunction('isFeatureGranted', fn(int $userId, string $feature): bool => $this->accessCheck->isFeatureGranted($userId, AccessGrantedFeatureEnum::from($feature), $this->game));
        $this->environment->addFunction($isFeatureGrantedFunction);

        $getUserAvatarFunction = new TwigFunction('getAvatar', fn(User|UserContainer $user): string => $user instanceof UserContainer
            ? $user->getAvatar()
            : $this->userSettingsProvider->getAvatar($user));
        $this->environment->addFunction($getUserAvatarFunction);

        $getRpgBehaviorFunction = new TwigFunction('getRpgBehavior', fn(User $user): UserRpgBehaviorEnum => $this->userSettingsProvider->getRpgBehavior($user));
        $this->environment->addFunction($getRpgBehaviorFunction);

        $isShowOnlineStateFunction = new TwigFunction('isShowOnlineState', fn(User $user): bool => $this->userSettingsProvider->isShowOnlineState($user));
        $this->environment->addFunction($isShowOnlineStateFunction);

        $hasAlliancePermissionFunction = new TwigFunction(
            'hasAlliancePermission',
            fn(string|array $permissionValue): bool => $this->checkAlliancePermission($permissionValue)
        );
        $this->environment->addFunction($hasAlliancePermissionFunction);
    }

    private function maskEmail(string $email): string
    {
        if (!$email || strpos($email, '@') === false) {
            return '';
        }

        $parts = explode('@', $email);
        $localPart = $parts[0];
        $domain = $parts[1];

        if (strlen($localPart) <= 2) {
            return $localPart[0] . '*@' . $domain;
        }

        return $localPart[0] . str_repeat('*', strlen($localPart) - 2) . $localPart[strlen($localPart) - 1] . '@' . $domain;
    }

    private function maskMobile(?string $mobile): string
    {
        if ($mobile === null || strlen($mobile) < 8) {
            return '';
        }

        $displayMobile = $mobile;
        if (strpos($mobile, '0049') === 0) {
            $displayMobile = '+49' . substr($mobile, 4);
        } elseif (strpos($mobile, '0043') === 0) {
            $displayMobile = '+43' . substr($mobile, 4);
        } elseif (strpos($mobile, '0041') === 0) {
            $displayMobile = '+41' . substr($mobile, 4);
        }

        if (strlen($displayMobile) > 8) {
            $start = substr($displayMobile, 0, 6);
            $end = substr($displayMobile, -2);
            $middle = str_repeat('*', strlen($displayMobile) - 8);
            return $start . $middle . $end;
        }

        return $displayMobile;
    }


    /**
     * @param string|array<string|int> $permissionValue
     */
    private function checkAlliancePermission(string|array $permissionValue): bool
    {
        $user = $this->game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            return false;
        }

        $permissionValues = is_array($permissionValue) ? $permissionValue : [$permissionValue];

        foreach ($permissionValues as $value) {
            $intValue = is_string($value) ? (int)$value : $value;
            $permissionEnum = AllianceJobPermissionEnum::tryFrom($intValue);
            if (
                $permissionEnum !== null
                && $this->allianceJobManager->hasUserPermission($user, $alliance, $permissionEnum)
            ) {
                return true;
            }
        }

        return false;
    }
}
