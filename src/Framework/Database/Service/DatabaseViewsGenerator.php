<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseViewsGeneratorException;
use OxidEsales\Facts\Facts;
use OxidEsales\DatabaseViewsGenerator\ViewsGenerator;

class DatabaseViewsGenerator implements DatabaseViewsGeneratorInterface
{
    /**
     * @var ViewsGenerator
     */
    private $viewsGenerator;

    /**
     * DatabaseViewsGenerator constructor.
     *
     * @param ViewsGenerator $viewsGenerator
     */
    public function __construct(ViewsGenerator $viewsGenerator)
    {
        $this->viewsGenerator = $viewsGenerator;
    }

    /**
     * @throws DatabaseViewsGeneratorException
     */
    public function generate(): void
    {
        $this->bootstrapOxidShopComponent();

        if (!$this->viewsGenerator->generate()) {
            throw new DatabaseViewsGeneratorException();
        }
    }

    private function bootstrapOxidShopComponent(): void
    {
        $bootstrapFilePath = (new Facts())->getSourcePath() . DIRECTORY_SEPARATOR . 'bootstrap.php';
        require_once $bootstrapFilePath;
    }
}
