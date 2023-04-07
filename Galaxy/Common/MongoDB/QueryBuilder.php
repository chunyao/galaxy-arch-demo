<?php

namespace Galaxy\Common\MongoDB;


use Galaxy\Common\Spl\Exception\Exception;

trait QueryBuilder
{

    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var string
     */
    protected $database = '';

    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var array
     */
    protected $join = [];

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var array
     */
    protected $group = [];

    /**
     * @var array
     */
    protected $having = [];

    /**
     * @var int
     */
    protected $offset = 0;

    /**
     * @var int
     */
    protected $limit = 0;

    /**
     * @var string
     */
    protected $lock = '';

    /**
     * @param string $table
     * @return $this
     */
    public function table(string $table): ConnectionInterface
    {

        $this->table = $table;
        return $this;
    }
    /**
     * @param string database
     * @return $this
     */
    public function database(string $database): ConnectionInterface
    {
        $this->database = $database;
        return $this;
    }

    public function tableSuffix(string $table, int $companyId, $subTable=100): ConnectionInterface
    {
        // 根据企业编号，对100取余分表
        $suffix = is_numeric($companyId) ? (int)$companyId % $subTable : null;
        $this->table = $table . $suffix;
        return $this;
    }


    /**
     * 设置返回字段
     * @param array $fields = ["name","age","username"]
     */
    public function field(array $fields)
    {
        $this->_fields = $fields;
        return $this;
    }
    /**
     * 设置屏蔽字段
     * @param array $fields = ["_id"]
     */
    public function unfield(array $fields)
    {
        $this->_unfields = $fields;
        return $this;
    }
    //对象转数组
    private function object2array($object)
    {
        return json_decode(json_encode($object), true);
    }

    public function limit($limit)
    {
        $this->_limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->_skip = $offset;
        return $this;
    }

    public function page($page = 1)
    {
        if ($page < 1) {
            $this->_page = 1;
        } else {
            $this->_page = $page;
        }

        $this->_skip = ($this->_page - 1) * $this->_limit;
        return $this;
    }

    public function sort($field, $isAsc = true)
    {
        $s = $isAsc ? 1 : -1;
        $this->_sort[$field] = $s;
        return $this;
    }

}