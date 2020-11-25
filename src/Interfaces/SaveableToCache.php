<?php

namespace Chistowick\Lettuce\Interfaces;

/**
 * Interface for saving to cache
 */
interface SaveableToCache
{
    public function toCache(): bool;
}
