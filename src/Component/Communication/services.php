<?php

declare(strict_types=1);

namespace Stu\Component\Communication;

use JBBCode\Parser;
use Stu\Component\Communication\Kn\KnBbCodeDefinitionSet;
use Stu\Component\Communication\Kn\KnFactory;
use Stu\Component\Communication\Kn\KnFactoryInterface;

use function DI\autowire;
use function DI\get;

return [
    'kn_bbcode_parser' => static function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new KnBbCodeDefinitionSet());
        return $parser;
    },
    KnFactoryInterface::class => autowire(KnFactory::class)->constructorParameter(
        'bbCodeParser',
        get('kn_bbcode_parser')
    )
];
