<?php

namespace Galaxy\Common\Utils;

use Galaxy\Core\Once;

/**
 * 雪花算法类
 * @package Galaxy\Common\Utils
 */
class SnowFlakeUtils
{
    const TWEPOCH = 1638288000000; // 时间起始标记点，作为基准，一般取系统的最近时间（一旦确定不能变动）
    const WORKER_ID_BITS = 5; // 机器标识位数
    const DATACENTER_ID_BITS = 5; // 数据中心标识位数
    const SEQUENCE_BITS = 12; // 毫秒内自增位
    // 工作机器ID
    private $workerId;
    /** 数据中心ID(0~31) */
    private $datacenterId;
    /** 毫秒内序列(0~4095) */
    private $sequence;
    // 机器ID最大值
    private $maxWorkerId = -1 ^ (-1 << self::WORKER_ID_BITS);
    // 数据中心ID最大值 最大31
    private $maxDatacenterId = -1 ^ (-1 << self::DATACENTER_ID_BITS);
    // 机器ID偏左移位数
    private $workerIdShift = self::SEQUENCE_BITS;
    // 数据中心ID左移位数 17 (12+5)
    private $datacenterIdShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS;
    /** 时间截向左移22位(5+5+12) */
    private $timestampLeftShift = self::SEQUENCE_BITS + self::WORKER_ID_BITS + self::DATACENTER_ID_BITS;
    /** 生成序列的掩码，这里为4095 (0b111111111111=0xfff=4095) */
    private $sequenceMask = -1 ^ (-1 << self::SEQUENCE_BITS);
    // 上次生产id时间戳
    private $lastTimestamp = -1;

    public function __construct($datacenterId, $workerId, $sequence = 0)
    {

        if ($workerId > $this->maxWorkerId || $workerId < 0) {
            throw new Exception("worker Id can't be greater than {$this->maxWorkerId} or less than 0");
        }
        if (!$datacenterId) {
            $datacenterId = $this->getDataCenterId();
        }
        if ($datacenterId > $this->maxDatacenterId || $datacenterId < 0) {
            throw new Exception("datacenter Id can't be greater than {$this->maxDatacenterId} or less than 0");
        }
        $this->workerId = $workerId;
        $this->datacenterId = $datacenterId;
        $this->sequence = $sequence;
    }

    public function nextId()
    {
        $timestamp = $this->timeGen();

        if ($timestamp < $this->lastTimestamp) {
            $diffTimestamp = bcsub($this->lastTimestamp, $timestamp);
            throw new Exception("Clock moved backwards.  Refusing to generate id for {$diffTimestamp} milliseconds");
        }

        if ($this->lastTimestamp == $timestamp) {
            $this->sequence = ($this->sequence + 1) & $this->sequenceMask;

            if (0 == $this->sequence) {
                $timestamp = $this->tilNextMillis($this->lastTimestamp);
            }
        } else {
            $this->sequence = 0;
        }

        $this->lastTimestamp = $timestamp;
        /*$gmpTimestamp    = gmp_init($this->leftShift(bcsub($timestamp, self::TWEPOCH), $this->timestampLeftShift));
        $gmpDatacenterId = gmp_init($this->leftShift($this->datacenterId, $this->datacenterIdShift));
        $gmpWorkerId     = gmp_init($this->leftShift($this->workerId, $this->workerIdShift));
        $gmpSequence     = gmp_init($this->sequence);
        return gmp_strval(gmp_or(gmp_or(gmp_or($gmpTimestamp, $gmpDatacenterId), $gmpWorkerId), $gmpSequence));*/
        //上次生成ID的时间截
        /*  Log::debug('SnowFlake:all', [
              $timestamp,
              self::TWEPOCH,
              $this->timestampLeftShift,
              $this->datacenterId,
              $this->datacenterIdShift,
              $this->workerId,
              $this->workerIdShift,
              $this->sequence,
          ]);*/
        return (($timestamp - self::TWEPOCH) << $this->timestampLeftShift) |
            ($this->datacenterId << $this->datacenterIdShift) |
            ($this->workerId << $this->workerIdShift) |
            $this->sequence;


    }

    protected function tilNextMillis($lastTimestamp)
    {
        $timestamp = $this->timeGen();
        while ($timestamp <= $lastTimestamp) {
            $timestamp = $this->timeGen();
        }
        return $timestamp;
    }

    protected function timeGen()
    {
        return floor(microtime(true) * 1000);
    }

    // 左移 <<
    protected function leftShift($a, $b)
    {
        return bcmul($a, bcpow(2, $b));
    }

    /*
     * Ip 取模
     *
     * */
    public static function getDataCenterId()
    {
        if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR']) {
            $cip = $_SERVER['REMOTE_ADDR'];
        } elseif (getenv("REMOTE_ADDR")) {
            $cip = getenv("REMOTE_ADDR");
        } elseif (getenv("HTTP_CLIENT_IP")) {
            $cip = getenv("HTTP_CLIENT_IP");
        } else {
            //获取不到ip 直接返回 随机取模数据 0-31 之间
            return rand(0, 31);
        }
        return intval(array_sum(explode('.', $cip)) % 32);
    }

    /**
     * 业务ID相关
     * @param start
     * @param end
     * @return
     */
    public static function getBizId($type)
    {
        $id = 0;
        switch ($type) {
            case 'companyId':
                $id = rand(0, 1);
                break;
            case 'ShopId':
                $id = rand(2, 5);
                break;
            case 'StockId':
                $id = rand(6, 13);
                break;
            case 'OrderId':
                $id = rand(14, 21);
                break;
            case 'OtherId':
                $id = rand(22, 31);
                break;
        }
        return $id;
    }
}
