<?php
namespace PHPCR\Test;

/**
 * Interface for fixture loader
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
     * @param string $fixture the fixtures "name", i.e. "general/base"
     * @param string $workspaceKey the config key for the target workspace, optional
     *
     * @return void
     */
    public function import($fixture, $workspaceKey = 'workspace');
}