<?php

declare(strict_types=1);

namespace App;

use App\Exception\SkipRowException;

class Parser
{
    const SYMFONY_VERSION = 5;

    private $blacklist;

    public function __construct()
    {
        $this->blacklist = require dirname(__DIR__).'/symfony-blacklist.php';
    }

    public function parse(array $row): array
    {
        if (!$this->shouldBeUsed($row)) {
            throw new SkipRowException();
        }

        return [
            'packageName' => $row[0],
            'type' => $row[1],
            'downloads' => (int) $row[3],
        ];
    }

    private function shouldBeUsed(array $row): bool
    {
        // Filter away packages with less than 100.000 downloads
        if (!isset($row[3]) || $row[3] < 100000) {
            return false;
        }

        if ('library' !== $row[1] && 'symfony-bundle' !== $row[1]) {
            return false;
        }

        if ($this->isSymfonyComponent($row[0])) {
            return false;
        }

        /*
         * The package looks good. Just make sure at least on dependency is a Symfony component
         */
        $dependencies = json_decode($row[2]);
        foreach ($dependencies as $packageName) {
            if ($this->isSymfonyComponent($packageName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if this package is a symfony component with version below SYMFONY_VERSION.
     */
    private function isSymfonyComponent($packageNameAndVersion): bool
    {
        // Package name must start with "symfony/"
        if ('symfony/' !== substr($packageNameAndVersion, 0, 8)) {
            return false;
        }

        // Ignore polyfill
        if ('symfony/polyfill-' === substr($packageNameAndVersion, 0, 17)) {
            return false;
        }

        // Ignore Contracts
        if (false !== strstr($packageNameAndVersion, '-contracts:')) {
            return false;
        }

        // Ignore Packs
        if (false !== strstr($packageNameAndVersion, '-pack:')) {
            return false;
        }

        list($packageName, $version) = explode(':', $packageNameAndVersion.':x', 2);
        if (in_array($packageName, $this->blacklist)) {
            return false;
        }

        if ('x' === $version[0]) {
            // $packageNameAndVersion did not contain any version
            return true;
        }

        /*
         * We need to figure out if the $version contains a major version number like SYMFONY_VERSION
         */
        // TODO implement version checks. (@see https://getcomposer.org/doc/articles/versions.md)

        return true;
    }
}
