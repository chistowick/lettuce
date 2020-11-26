<?php

namespace Chistowick\Lettuce\Interfaces;

/**
 * Interface for saving to MySQL
 */
interface SaveableToMysql
{
    public function toMysql(): void;
}
