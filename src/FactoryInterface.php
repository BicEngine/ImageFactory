<?php

declare(strict_types=1);

namespace Bic\Image\Factory;

use Bic\Image\Factory\Exception\FileNonReadableException;
use Bic\Image\Factory\Exception\FileNotFoundException;
use Bic\Image\Factory\Exception\NonDecodableException;
use Bic\Image\Factory\Exception\NonReadableException;
use Bic\Image\Factory\Exception\NonSeekableException;
use Bic\Image\FileImageInterface;
use Bic\Image\ImageInterface;

interface FactoryInterface
{
    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the "rb" mode.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @psalm-taint-sink file $filename
     * @param non-empty-string $filename Filename or stream URI to use as basis
     *        of image file.
     *
     * @return iterable<FileImageInterface>
     *
     * @throws FileNonReadableException In case of file cannot be read.
     * @throws FileNotFoundException In case of file not found.
     * @throws NonDecodableException In case of image non-decodable.
     */
    public function createImageFromFile(string $filename): iterable;

    /**
     * Create a new {@see ImageInterface} instance from an existing resource.
     *
     * The stream MUST be readable and seekable, and may be writable.
     *
     * @param resource $stream PHP resource stream to use as basis of image.
     *
     * @return iterable<ImageInterface>
     *
     * @throws NonReadableException In case of resource stream not readable.
     * @throws NonSeekableException In case of resource stream not seekable.
     * @throws NonDecodableException In case of image non-decodable.
     */
    public function createImageFromResource(mixed $stream): iterable;

    /**
     * Create a new {@see ImageInterface} instance from a string.
     *
     * @param string $contents String content with which to populate the image.
     *
     * @return iterable<ImageInterface>
     *
     * @throws NonDecodableException In case of image non-decodable.
     */
    public function createImageFromString(string $contents): iterable;
}
