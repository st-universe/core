<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use JBBCode\Parser;
use Stu\Module\Tal\TalHelper;
use Twig\Environment;
use Twig\TwigFilter;

class TwigHelper
{
    private Environment $environment;

    private Parser $parser;

    public function __construct(
        Environment $environment,
        Parser $parser
    ) {
        $this->environment = $environment;
        $this->parser = $parser;
    }

    /**
     * Registers global available twig methods and filters
     */
    public function registerMethodsAndFilters(): void
    {
        $bbcode2txtFilter = new TwigFilter('bbcode2txt', function ($string) {
            return $this->parser->parse($string)->getAsText();
        });
        $this->environment->addFilter($bbcode2txtFilter);

        $bbcodeFilter = new TwigFilter('bbcode', function ($string) {
            return $this->parser->parse($string)->getAsHTML();
        });
        $this->environment->addFilter($bbcodeFilter);

        $jsquoteFilter = new TwigFilter('jsquote', function ($string) {
            return TalHelper::jsquote($string);
        });
        $this->environment->addFilter($jsquoteFilter);

        $addPlusCharacterFilter = new TwigFilter('addPlusCharacter', function ($value) {
            if (is_integer($value)) {
                return TalHelper::addPlusCharacter(strval($value));
            }
            return TalHelper::addPlusCharacter($value);
        });
        $this->environment->addFilter($addPlusCharacterFilter);
    }
}
