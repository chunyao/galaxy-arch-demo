<?php

namespace Galaxy\Common\Mysql;
use Galaxy\Core\Log;
/**
 * Class DBLogger
 * @package App\Container
 */
class DBLogger implements \Mix\Database\LoggerInterface
{

    /**
     * @param float $time
     * @param string $sql
     * @param array $bindings
     * @param int $rowCount
     * @param \Throwable|null $exception
     * @return void
     */
    public function trace(float $time, string $sql, array $bindings, int $rowCount, ?\Throwable $exception): void
    {
      #  Log::debug(sprintf('SQL: %sms %s %s %d', $time, $sql, json_encode($bindings), $rowCount));
    }

}
