<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\DeveloperTools\Framework\Database\Service;

use OxidEsales\DeveloperTools\Framework\Database\Exception\DatabaseViewsGeneratorException;

interface DatabaseViewsGeneratorInterface
{
    /**
     * @throws DatabaseViewsGeneratorException
     */
    public function generate(): void;
}
