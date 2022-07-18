<?php

namespace App\Service;

use App\Repository\Model\ImproveSqlModel;
use Galaxy\Service\BaseService;

class ImproveSqlService extends BaseService
{
    public function improveSql():array
    {
        return ImproveSqlModel::instance()->findByCompanyId(212548);
    }
    public function sycncByCompanyId():array
    {
        return ImproveSqlModel::instance()->sycncByCompanyId(212548);
    }

    function leftJoin($array1, $array2, $field1, $field2 = '')
    {
        $ret = array();
        //位数
        //left join 使用 field1
        foreach ($array2 as $key => $value) {
            $array3[$value[$field1]] = $value;
        }
        foreach ($array1 as $key => $value) {
            $ret[] = array_merge($array3[$value[$field1]], $value);
        }
        return $ret;
    }

}