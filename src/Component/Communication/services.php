<?php

declare(strict_types=1);

namespace Stu\Component\Communication;

use JBBCode\Parser;
use Stu\Component\Communication\Kn\KnBbCodeDefinitionSet;
use Stu\Component\Communication\Kn\KnFactory;
use Stu\Component\Communication\Kn\KnFactoryInterface;
use Stu\Orm\Repository\KnCommentRepositoryInterface;

use function DI\create;
use function DI\get;

return [
    'kn_bbcode_parser' => static function (): Parser {
        $parser = new Parser();
        $parser->addCodeDefinitionSet(new KnBbCodeDefinitionSet());
        return $parser;
    },
    KnFactoryInterface::class => create(KnFactory::class)->constructor(
        get('kn_bbcode_parser'),
        get(KnCommentRepositoryInterface::class)
    ),
];
