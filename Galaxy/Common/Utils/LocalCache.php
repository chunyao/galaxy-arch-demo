<?php
namespace Galaxy\Common\Utils;

use Swoole;

class LocalCache
{
    protected $table;

    public function __construct()
    {
        $this->table = new Swoole\Table(8388608);
        $this->table->column('id', Swoole\Table::TYPE_INT);
        $this->table->column('ttl', Swoole\Table::TYPE_INT);
        $this->table->column('ctime', Swoole\Table::TYPE_INT);
        $this->table->column('data', Swoole\Table::TYPE_STRING,64);
        $this->table->create();
    }

    public function set(string $key,  $data, int $ttl = 0): bool
    {
        if (empty($ttl)){
            $ttl=30;
        }
        $value = array();
        $value['ctime'] = time();
        $value['ttl'] = $ttl;
        $value['data'] = json_encode($data);
        return $this->table->set($key, $value);
    }

    public function get(string $key)
    {
        if ($tmp = $this->table->get($key)) {
            if (time() < ($tmp['ctime'] + $tmp['ttl'])) {
                return json_decode($tmp['data']);
            }
        }
        return false;
    }

    public function del(string $key)
    {
        return $this->table->del($key);
    }

    public function incr()
    {

    }

    public function decr()
    {

    }

    public function exist()
    {

    }

    public function count()
    {
        return $this->table->count();
    }
}
