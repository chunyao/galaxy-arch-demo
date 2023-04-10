<?php

namespace Galaxy\Common\MongoDB;


use Hyperf\GoTask\MongoClient\Type\BulkWriteResult;
use Hyperf\GoTask\MongoClient\Type\DeleteResult;
use Hyperf\GoTask\MongoClient\Type\UpdateResult;

interface ConnectionInterface
{
    public function table(string $table): ConnectionInterface;

    public function tableSuffix(string $table, int $companyId, $subTable = 100): ConnectionInterface;

    public function find($where = [], $isArray = true);

    public function select($where = [], $isArray = true);

    public function insert(array $data, $keepIdColumn = false, &$insertId);

    public function insertAll(array $data, $keepIdColumn = false);

    public function drop();

    public function dropIndexes(array $opts = []);

    public function dropIndex(string $name, array $opts = []);

    public function listIndexes($indexes = [], array $opts = []): array;

    public function createIndexes($indexes = [], array $opts = []): array;

    public function createIndex($index = [], array $opts = []): string;

    public function distinct(string $fieldName, $filter = [], array $opts = []);

    public function bulkWrite($operations = [], array $opts = []): BulkWriteResult;

    public function aggregate($pipeline = [], array $opts = []);

    public function deleteMany($filter = [], array $opts = []): DeleteResult;

    public function deleteOne($filter = [], array $opts = []): DeleteResult;

    public function countDocuments($filter = [], array $opts = []): int;

    public function replaceOne($filter = [], $replace = [], array $opts = []): UpdateResult;

    public function updateMany($filter = [], $update = [], array $opts = []): UpdateResult;

    public function updateOne($filter = [], $update = [], array $opts = []): UpdateResult;

    public function findOneAndReplace($filter = [], $replace = [], array $opts = []);

    public function findOneAndUpdate($filter = [], $update = [], array $opts = []);

    public function findOneAndDelete($filter = [], array $opts = []);

    public function findOne($filter = [], array $opts = []);

    public function field(array $fields);

    public function unfield(array $fields);

    public function page($page = 1);

    public function sort($field, $isAsc = true);

}
