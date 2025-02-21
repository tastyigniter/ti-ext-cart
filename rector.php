<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictNewArrayRector;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withImportNames(removeUnusedImports: true)
    ->withPaths([__DIR__.'/src', __DIR__.'/tests'])
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    ->withSkip([
        ReturnTypeFromStrictNewArrayRector::class,
    ])
    ->withTypeCoverageLevel(100)
    ->withDeadCodeLevel(100)
    ->withCodeQualityLevel(100);
