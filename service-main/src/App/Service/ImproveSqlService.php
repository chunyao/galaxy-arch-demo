<?php

namespace App\Service;

use App\Repository\Model\ImproveSqlModel;
use Mabang\Galaxy\Service\BaseService;

class ImproveSqlService extends BaseService
{
    public function improveSql(): array
    {
        return ImproveSqlModel::instance()->findByCompanyId(212548);
    }

    public function sycncByCompanyId(): array
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

    function multi_array_sort($multi_array, $sort_key, $sort = SORT_DESC)
    {
        if (is_array($multi_array)) {
            foreach ($multi_array as $row_array) {
                if (is_array($row_array)) {
                    $key_array[] = $row_array[$sort_key];

                } else {
                    return FALSE;

                }

            }

        } else {
            return FALSE;

        }

        array_multisort($key_array, $sort, $multi_array);

        return $multi_array;

    }

}