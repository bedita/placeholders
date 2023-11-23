<?php
declare(strict_types=1);

namespace BEdita\Placeholders\Test\TestApp\Filesystem\Adapter;

use BEdita\Core\Filesystem\FilesystemAdapter;
use League\Flysystem\FilesystemAdapter as LeagueFilesystemAdapter;

/**
 * Null adapter, for testing.
 */
class NullAdapter extends FilesystemAdapter
{
    /**
     * @inheritDoc
     */
    protected function buildAdapter(array $config)
    {
        return new LeagueFilesystemAdapter();
    }
}
