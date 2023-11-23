<?php
declare(strict_types=1);

namespace BEdita\Placeholders\Test\TestApp\Filesystem\Adapter;

use BEdita\Core\Filesystem\FilesystemAdapter;
use League\Flysystem\Config;
use League\Flysystem\FileAttributes;
use League\Flysystem\FilesystemAdapter as FlysystemFilesystemAdapter;

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
        return new class () implements FlysystemFilesystemAdapter
        {
            public function fileExists(string $path): bool
            {
                return false;
            }

            public function write(string $path, string $contents, Config $config): void
            {
            }

            public function writeStream(string $path, $contents, Config $config): void
            {
            }

            public function read(string $path): string
            {
                return '';
            }

            public function readStream(string $path)
            {
                /** @var resource $stream */
                $stream = \fopen('php://temp', 'w+b');
                \fwrite($stream, '');
                \rewind($stream);

                return $stream;
            }

            public function delete(string $path): void
            {
            }

            public function deleteDirectory(string $path): void
            {
            }

            public function createDirectory(string $path, Config $config): void
            {
            }

            public function setVisibility(string $path, string $visibility): void
            {
            }

            public function visibility(string $path): FileAttributes
            {
                return new FileAttributes('');
            }

            public function mimeType(string $path): FileAttributes
            {
                return new FileAttributes('');
            }

            public function lastModified(string $path): FileAttributes
            {
                return new FileAttributes('');
            }

            public function fileSize(string $path): FileAttributes
            {
                return new FileAttributes('');
            }

            public function listContents(string $path, bool $deep): iterable
            {
                return [];
            }

            public function move(string $source, string $destination, Config $config): void
            {
            }

            public function copy(string $source, string $destination, Config $config): void
            {
            }
        };
    }
}
