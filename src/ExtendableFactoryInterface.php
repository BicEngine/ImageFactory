<?php

declare(strict_types=1);

namespace Bic\Image\Factory;

use Bic\Image\DecoderInterface;

interface ExtendableFactoryInterface extends FactoryInterface
{
    /**
     * Register a custom decoder.
     */
    public function extend(DecoderInterface $decoder): void;
}
