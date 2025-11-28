<?php
declare(strict_types=1);

namespace BEdita\Placeholders\Test\TestApp\Filesystem\Adapter;

use BEdita\Core\Filesystem\FilesystemAdapter;
use League\Flysystem\FilesystemAdapter as LeagueFilesystemAdapter;
use League\Flysystem\InMemory\InMemoryFilesystemAdapter;

/**
 * Null adapter, for testing.
 * Internally it uses `\League\Flysystem\InMemory\InMemoryFilesystemAdapter`
 *
 * @see https://flysystem.thephpleague.com/docs/adapter/in-memory/
 */
class NullAdapter extends FilesystemAdapter
{
    /**
     * {@inheritDoc}
     *
     * @param array<string, mixed> $config
     */
    protected function buildAdapter(array $config): LeagueFilesystemAdapter
    {
        return new InMemoryFilesystemAdapter();
    }
}
