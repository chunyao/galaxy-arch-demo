<?php

namespace Galaxy\Common\Utils;

/**
 * 雪花算法类
 * @package Galaxy\Common\Utils
 */
class SnowFlakeUtils
{
    const EPOCH_OFFSET = 0;  //偏移时间戳,该时间一定要小于第一个id生成的时间,且尽量大(影响算法的有效可用时间)

    const SIGN_BITS = 1;        //最高位(符号位)位数，始终为0，不可用
    const TIMESTAMP_BITS = 41;  //时间戳位数(算法默认41位,可以使用69年)
    const DATA_CENTER_BITS = 5;  //IDC(数据中心)编号位数(算法默认5位,最多支持部署32个节点)
    const MACHINE_ID_BITS = 5;  //机器编号位数(算法默认5位,最多支持部署32个节点)
    const SEQUENCE_BITS = 12;   //计数序列号位数,即一系列的自增id，可以支持同一节点同一毫秒生成多个ID序号(算法默认12位,支持每个节点每毫秒产生4096个ID序号)。

    /**
     * @var integer 数据中心编号
     */
    protected static $data_center_id;

    /**
     * @var integer 机器编号
     */
    protected static $machine_id;

    /**
     * @var null|integer 上一次生成id使用的时间戳(毫秒级别)
     */
    protected static $lastTimestamp = null;

    /**
     * @var int
     */
    protected static $sequence = 1;    //序列号
    protected static $signLeftShift = self::TIMESTAMP_BITS + self::DATA_CENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;  //符号位左位移位数
    protected static $timestampLeftShift = self::DATA_CENTER_BITS + self::MACHINE_ID_BITS + self::SEQUENCE_BITS;    //时间戳左位移位数
    protected static $dataCenterLeftShift = self::MACHINE_ID_BITS + self::SEQUENCE_BITS;   //IDC左位移位数
    protected static $machineLeftShift = self::SEQUENCE_BITS;  //机器编号左位移位数
    protected static $maxSequenceId = -1 ^ (-1 << self::SEQUENCE_BITS);    //最大序列号
    protected static $maxMachineId = -1 ^ (-1 << self::MACHINE_ID_BITS);   //最大机器编号
    protected static $maxDataCenterId = -1 ^ (-1 << self::DATA_CENTER_BITS);   //最大数据中心编号

    /**
     * @param integer $dataCenter_id 数据中心的唯一ID(如果使用多个数据中心,需要设置此ID用以区分)
     * @param integer $machine_id 机器的唯一ID (如果使用多台机器,需要设置此ID用以区分)
     * @throws \Exception
     */
    public static function init($dataCenter_id = 0, $machine_id = 0)
    {
        if ($dataCenter_id > self::$maxDataCenterId) {
            throw new \Exception('数据中心编号取值范围为:0-' . self::$maxDataCenterId);
        }
        if ($machine_id > self::$maxMachineId) {
            throw new \Exception('机器编号编号取值范围为:0-' . self::$maxMachineId);
        }
        self::$data_center_id = $dataCenter_id;
        self::$machine_id = $machine_id;
    }

    /**
     * 使用雪花算法生成一个唯一ID
     * @return string 生成的ID
     * @throws \Exception
     */
    public static function generateID($dataCenter_id = 0, $machine_id = 0)
    {
        self::init($dataCenter_id, $machine_id);
        $sign = 0; //符号位,值始终为0
        $timestamp = self::getUnixTimestamp();
        if ($timestamp < self::$lastTimestamp) {
            throw new \Exception('时间倒退了!');
        }

        //与上次时间戳相等,需要生成序列号.不相等则重置序列号
        if ($timestamp == self::$lastTimestamp) {
            $sequence = ++self::$sequence;
            if ($sequence == self::$maxSequenceId) { //如果序列号超限，则需要重新获取时间
                $timestamp = self::getUnixTimestamp();
                while ($timestamp <= self::$lastTimestamp) {    //时间相同则阻塞
                    $timestamp = self::getUnixTimestamp();
                }
                self::$sequence = 0;
                $sequence = ++self::$sequence;
            }
        } else {
            self::$sequence = 0;
            $sequence = ++self::$sequence;
        }

        self::$lastTimestamp = $timestamp;
        $time = (int)($timestamp - self::EPOCH_OFFSET);
        $id = ($sign << self::$signLeftShift) | ($time << self::$timestampLeftShift) | (self::$data_center_id << self::$dataCenterLeftShift) | (self::$machine_id << self::$machineLeftShift) | $sequence;

        return (string)$id;
    }

    /**
     * 获取去当前时间戳
     *
     * @return integer 毫秒级别的时间戳
     */
    private static function getUnixTimestamp()
    {
        return floor(microtime(true) * 1000);
    }
}