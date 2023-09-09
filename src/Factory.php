<?php

declare(strict_types=1);

namespace Bic\Image\Factory;

use Bic\Image\DecoderInterface;
use Bic\Image\Factory\Exception\FileNonReadableException;
use Bic\Image\Factory\Exception\FileNotFoundException;
use Bic\Image\Factory\Exception\NonDecodableException;
use Bic\Image\Factory\Exception\NonReadableException;
use Bic\Image\Factory\Exception\NonSeekableException;
use Bic\Image\FileImage;

final class Factory implements ExtendableFactoryInterface
{
    /**
     * @var \SplObjectStorage<DecoderInterface, mixed>
     */
    private readonly \SplObjectStorage $decoders;

    /**
     * @param iterable<DecoderInterface> $decoders
     */
    public function __construct(iterable $decoders = [])
    {
        $this->decoders = new \SplObjectStorage();

        foreach ($decoders as $decoder) {
            $this->extend($decoder);
        }
    }

    public function extend(DecoderInterface $decoder): void
    {
        $this->decoders->attach($decoder);
    }

    public function createImageFromFile(string $filename): iterable
    {
        $filename = \realpath($filename) ?: $filename;

        if (!\is_file($filename)) {
            throw new FileNotFoundException(\sprintf('File "%s" not found', $filename));
        }

        if (!\is_readable($filename)) {
            throw new FileNonReadableException(\sprintf('File "%s" not readable', $filename));
        }

        $stream = \fopen($filename, 'rb');

        try {
            foreach ($this->createImageFromResource($stream) as $image) {
                yield new FileImage($filename, $image);
            }
        } finally {
            \fclose($stream);
        }
    }

    public function createImageFromResource(mixed $stream): iterable
    {
        assert(\is_resource($stream));

        $meta = \stream_get_meta_data($stream);

        if (!\str_contains($meta['mode'], 'r') && !\str_contains($meta['mode'], '+')) {
            throw new NonReadableException('Passed resource stream is not readable');
        }

        if (!$meta['seekable']) {
            throw new NonSeekableException('Passed resource stream is not seekable');
        }

        return $this->decode($stream);
    }

    private function decode(mixed $stream): iterable
    {
        $seek = \ftell($stream);

        foreach ($this->decoders as $decoder) {
            \fseek($stream, $seek);

            if (($result = $decoder->decode($stream)) !== null) {
                return $result;
            }
        }

        throw new NonDecodableException('Cannot find the suitable decoder for the image');
    }

    public function createImageFromString(string $contents): iterable
    {
        $stream = \fopen('php://memory', 'rb+');

        \fwrite($stream, $contents);
        \rewind($stream);

        try {
            return $this->createImageFromResource($stream);
        } finally {
            \fclose($stream);
        }
    }
}
