<?php
namespace Galaxy\Common\MongoDB;


interface ConnectionInterface
{
    public function table_pool($table);
    public function tableSuffix_pool(string $table, int $companyId, $subTable = 100);
}
