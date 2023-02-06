<?php

use Rector\Config\RectorConfig;
use Rector\Core\ValueObject\PhpVersion;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Symfony\Set\SymfonySetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([__DIR__ . '/src']);
    $rectorConfig->phpVersion(PhpVersion::PHP_81);
    $rectorConfig->autoloadPaths([__DIR__ . '/migrations']);
    $rectorConfig->symfonyContainerXml(
        __DIR__ . '/var/cache/dev/App_KernelDevDebugContainer.xml',
    );

    $rectorConfig->import(LevelSetList::UP_TO_PHP_81);
    $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
};
