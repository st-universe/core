<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use JBBCode\Parser;
use Noodlehaus\ConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\Battle\FightLibInterface;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Tal\TalHelper;
use Stu\Orm\Entity\ShipInterface;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;

class TwigHelper
{
    private Environment $environment;

    private Parser $parser;

    private ConfigInterface $config;

    private FightLibInterface $fightLib;

    public function __construct(
        Environment $environment,
        Parser $parser,
        ConfigInterface $config,
        FightLibInterface $fightLib
    ) {
        $this->environment = $environment;
        $this->parser = $parser;
        $this->config = $config;
        $this->fightLib = $fightLib;
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

        $datetimeFilter = new TwigFilter('datetime', function ($value): string {
            return sprintf(
                '%s%s %s',
                date('d.m.', $value),
                (int)date("Y", $value) + StuTime::STU_YEARS_IN_FUTURE_OFFSET,
                date("H:i", $value)
            );
        });
        $this->environment->addFilter($datetimeFilter);
    }

    private function registerFunctions(): void
    {
        $canAttackTargetFunction = new TwigFunction('canAttackTarget', function (ShipInterface $ship, ShipInterface|ShipNfsItem $target): bool {
            return $this->fightLib->canAttackTarget($ship, $target);
        });
        $this->environment->addFunction($canAttackTargetFunction);
    }
}
