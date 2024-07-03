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
use Rector\Php80\Rector\Catch_\RemoveUnusedVariableInCatchRector;
use Rector\Php80\Rector\Class_\ClassPropertyAssignToConstructorPromotionRector;
use Rector\Php80\Rector\FunctionLike\MixedTypeRector;
use Rector\Php80\Rector\Switch_\ChangeSwitchToMatchRector;
use Rector\Php83\Rector\ClassConst\AddTypeToConstRector;
use Rector\Php83\Rector\ClassMethod\AddOverrideAttributeToOverriddenMethodsRector;

/**
 * Execute on command line via 'vendor/bin/rector process'
 */
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src/admin',
        __DIR__ . '/src/Component',
        __DIR__ . '/src/Config',
        __DIR__ . '/src/Exception',
        __DIR__ . '/src/Lib',
        __DIR__ . '/src/Module',
        __DIR__ . '/src/Orm',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->importNames();

    $rectorConfig->sets([
        //SetList::CODE_QUALITY,        //last 2024-07-03
        //SetList::CODING_STYLE,
        //SetList::DEAD_CODE,           //last 2024-07-03
        //SetList::PRIVATIZATION,       //lots of errors
        //SetList::TYPE_DECLARATION,    //last 2023-07-17
        //LevelSetList::UP_TO_PHP_74,   //last 2023-07-18
        //LevelSetList::UP_TO_PHP_83,
        //DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES //last 2023-12-13
    ]);

    $rectorConfig->rules([
        AddOverrideAttributeToOverriddenMethodsRector::class,
        AddTypeToConstRector::class,
        RemoveUnusedVariableInCatchRector::class,
        ClassPropertyAssignToConstructorPromotionRector::class,
        ChangeSwitchToMatchRector::class,
        MixedTypeRector::class
        //FinalPrivateToPrivateVisibilityRector::class
        //MyCLabsClassToEnumRector::class
    ]);

    $rectorConfig->skip([
        SimplifyBoolIdenticalTrueRector::class,
        FlipTypeControlToUseExclusiveTypeRector::class,
        PostIncDecToPreIncDecRector::class,
        __DIR__ . '/src/OrmProxy'
    ]);

    /**
    $rectorConfig->ruleWithConfiguration(ConsistentPregDelimiterRector::class, [
        ConsistentPregDelimiterRector::DELIMITER => '/',
    ]); */

    //$rectorConfig->parallel(?,?,?)
};
