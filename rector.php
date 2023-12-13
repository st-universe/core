<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Identical\FlipTypeControlToUseExclusiveTypeRector;
use Rector\CodeQuality\Rector\Identical\SimplifyBoolIdenticalTrueRector;
use Rector\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector;
use Rector\CodingStyle\Rector\PostInc\PostIncDecToPreIncDecRector;
use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Doctrine\Set\DoctrineSetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/Orm',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->importNames();

    $rectorConfig->sets([
        //SetList::CODE_QUALITY,        //last 2023-07-18
        //SetList::CODING_STYLE,
        //SetList::DEAD_CODE,           //last 2023-07-18
        //SetList::PRIVATIZATION,       //lots of errors
        //SetList::TYPE_DECLARATION,    //last 2023-07-17
        //LevelSetList::UP_TO_PHP_74,   //last 2023-07-18
        //LevelSetList::UP_TO_PHP_82,
        DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES //last 2023-12-13
    ]);

    $rectorConfig->skip([
        SimplifyBoolIdenticalTrueRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PostIncDecToPreIncDecRector::class,
        __DIR__ . '/src/OrmProxy'
    ]);

    $rectorConfig->ruleWithConfiguration(ConsistentPregDelimiterRector::class, [
        ConsistentPregDelimiterRector::DELIMITER => '/',
    ]);

    //$rectorConfig->parallel(?,?,?)
};
