<?php

/*
 * This file is part of the PHPCR API Tests package
 *
 * Copyright (c) 2015 Liip and others
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PHPCR\Test;

/**
 * Interface for fixture loader.
 */
interface FixtureLoaderInterface
{
    /**
     * Load fixture data into your implementation to prepare for a test.
     *
     * The list of possible fixture names can be derived from the list of xml
     * files in fixtures. The names are the relative paths without the .xml
     * extension
     *
     * Default fixtures in the jcr system view format live folder fixtures/
     *
     * @param string $fixture      the fixtures "name", i.e. "general/base"
     * @param string $workspaceKey the config key for the target workspace, optional
     */
    public function import($fixture, $workspaceKey = 'workspace');
}
