<?php
namespace Mabang\Galaxy\Common\Utils;

use Swoole;
 
class LocalCache
{
    protected $table;

    public function __construct()
    {
        $this->table = new Swoole\Table(16192);
        $this->table->column('id', Swoole\Table::TYPE_INT);
        $this->table->column('ttl', Swoole\Table::TYPE_INT);
        $this->table->column('ctime', Swoole\Table::TYPE_INT);
        $this->table->column('data', Swoole\Table::TYPE_STRING,128);
        $this->table->column('num', Swoole\Table::TYPE_INT);
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
    public function setIncr(string $key, int $ttl = 0): bool
    {
        if (empty($ttl)){
            $ttl=30;
        }
        $value = array();
        $value['ctime'] = time();
        $value['ttl'] = $ttl;
        $value['num'] = 0;
        return $this->table->set($key, $value);
    }

    public function get(string $key)
    {
        if ($tmp = $this->table->get($key)) {
            if (time() < ($tmp['ctime'] + $tmp['ttl'])) {
                return json_decode($tmp['data']);
            }else{
                $this->table->del($key);
            }
        }
        return false;
    }

    public function getIncr(string $key)
    {
        if ($tmp = $this->table->get($key)) {
            return $tmp['num'];
        }
        return false;
    }

    public function del(string $key)
    {
        return $this->table->del($key);
    }

    public function incr(string $key,$incrby = 1):int
    {
        return $this->table->incr($key,'num',$incrby);
    }

    public function decr(string $key,$incrby = 1):int
    {
        return $this->table->decr($key,'num',$incrby);
    }

    public function exist($key):bool
    {
        return $this->table->exist($key);
    }

    public function count()
    {
        return $this->table->count();
    }
    public function removeTimeOut()
    {
        foreach ($this->table as $key=>$val){
            if (isset($val['ttl'])){
                if (time() >= ($val['ctime'] + $val['ttl'])) {
                    $this->table->del($key);
                }
            }
        }
        return true;
    }
}
