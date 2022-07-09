<?php

namespace Galaxy\Common\ES;

use Elasticsearch\ClientBuilder;


/**
 * 查询语句文档
 * https://www.elastic.co/guide/cn/elasticsearch/guide/current/index.html
 * 示例:
 * $es = LibES::getInstance(['host'=>'','user'=>'','password'=>'']，'test_index'); //获取test_index索引实例
 * $es->setLimit(0, 20);
 * $es->setOrder(['abc' => 'desc']);
 * $rs = $es->search(['match' => ['abc' => 'abc']]);
 */

/**
 * Class LibES
 * @package SDK\Library
 */
class LibES
{

    const RETRY_COUNT = 1;

    const MASTER_TIMEOUT = '1s';

    const TIME_OUT = '1s';

    const DEFAULT_SCORE = 0.3;

    const DEFAULT_OFFSET = 0;

    const DEFAULT_LIMIT = 20;

    const DEFAULT_ORDER = '_score';

    private static $instance = [];

    private $esClient = null;

    private $indexName = null;

    private $typeName = null;

    private $offset = self::DEFAULT_OFFSET;

    private $limit = self::DEFAULT_LIMIT;

    private $order = [self::DEFAULT_ORDER => ['order' => 'desc']];

    private $score = self::DEFAULT_SCORE;

    private $aggs = [];

    private $routing = null;
    private static $multiBody = [];



    /**
     * LibES constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->esClient = $this->bulidClient($config['es.host'], $config['es.user'], $config['es.password']);
        $this->indexName = $config['es.index_name'];
        $this->typeName = $config['es.type_name'];
        //  $this->createIndex();

    }

    private function bulidClient($host, $user, $pass)
    {
        $host = ['host' => $host];
        !empty($user) && $host['user'] = $user;
        !empty($pass) && $host['pass'] = $pass;
        return ClientBuilder::create()->setHosts([$host])
            ->setRetries(self::RETRY_COUNT)->build();
    }

    /**
     * @return bool
     */
    public function existsIndex()
    {
        $params = ['index' => $this->indexName];
        return $this->esClient->indices()->exists($params);
    }

    public function createIndex()
    {
        if ($this->existsIndex()) {
            return true;
        }

        $params = [
            'index' => $this->indexName,
            'master_timeout' => self::MASTER_TIMEOUT,
            'timeout' => self::TIME_OUT
        ];
        return $this->esClient->indices()->create($params);
    }

    public function deleteIndex($indexName)
    {
        if (!$this->existsIndex()) {
            return true;
        }
        $params = ['index' => $this->indexName];
        return $this->esClient->indices()->delete($params);
    }

    public function getIndexData()
    {
        if (!$this->existsIndex()) {
            return false;
        }

        $params = ['index' => $this->indexName];
        return $this->esClient->indices()->getMapping($params);
    }


    public function insertDocument($body, $esID = null)
    {
        if (!$this->existsIndex()) {
            return false;
        }

        if (!is_array($body) || empty($body)) {
            return false;
        }

        $params = [
            'index' => $this->indexName,
            'type' => $this->typeName,
            'routing' => $this->routing,
            'body' => $body
        ];
        $esID !== null && $params['id'] = $esID;

        return $this->esClient->create($params);
    }

    public function updateDocumentById($esID, $body)
    {
        if (!$this->existsIndex()) {
            return false;
        }

        if (!is_array($body) || empty($body) || empty($esID)) {
            return false;
        }

        $params = [
            'index' => $this->indexName,
            'type' =>$this->typeName,
            'id' => $esID
        ];
        $params['body']['doc'] = $body;

      //  $startTime = microtime(true);
        $rs = $this->esClient->update($params);
        /*$logData = [
            'method' => __FUNCTION__,
            'result' => $rs,
            'exec_time' => round(microtime(true) - $startTime, 3)
        ];
        $logData = array_merge($logData, $params);
        Log::info('ELASTIC', $logData);*/
        return $rs;
    }

    /**
     * 根据查询更新
     * @param array $where ['hw_id'=> 1, 'account_id'=>3]  or [ [ 'a'=>1],['a'=>2]]
     * @param string $field
     * @return array|bool
     */
    public function updateByQuery(array $where, $field)
    {

        if (!$this->existsIndex()) {
            return false;
        }

        if (empty($field) || !$where) {
            return false;
        }
        $fields = $fieldes = '';
        if (is_string($field)) {
            $fields = $field;
        } elseif (is_array($field)) {

            foreach ($field as $k => $v) {
                if (is_string($v)) {
                    $fields .= 'ctx._source' . '.' . $k . '=' . '\"' . $v . '\";';
                } else {
                    $fieldes .= "ctx._source.{$k}=$v;";
                }
            }
            $fields = $fields . ' ' . $fieldes;
            $fields = stripslashes(rtrim($fields, ";"));
        }else{
            return false;
        }

        $body = [];
        foreach ($where as $key => $val) {
            if (!is_array($val)) {
                $body[] = ['term' => [$key => $val]];
            } else {
                $body[] = ['terms' => [$key => $val]];
            }
        }

        $query['query']['bool']['must'] = $body;
        $query ['script'] = [
            'inline' => $fields,
         //   'lang' => 'painless'
        ];

        $params = [
            'index' => $this->indexName,
            'body' => $query,
        ];

        if ($this->routing) {
            $params['routing'] = $this->routing;
        }
        return $this->esClient->updateByQuery($params);
    }

    public function getDocumentById($esID)
    {
        if (!$this->existsIndex()) {
            return false;
        }

        if (empty($esID)) {
            return false;
        }
        $params = [
            'index' => $this->indexName,
            'id' => $esID
        ];

       // $startTime = microtime(true);
        $rs = $this->esClient->get($params);
        /*$logData = [
            'method' => __FUNCTION__,
            'result' => $rs,
            'exec_time' => round(microtime(true) - $startTime, 3)
        ];
        $logData = array_merge($logData, $params);
        Log::info('ELASTIC', $logData);*/
        return $rs;
    }

    public function deleteDocumentById($esID)
    {
        if (!$this->existsIndex()) {
            return false;
        }

        if (empty($esID)) {
            return false;
        }
        $params = [
            'index' => $this->indexName,
            'type' => $this->typeName,
            'id' => $esID
        ];

        //$startTime = microtime(true);
        $rs = $this->esClient->delete($params);
       /* $logData = [
            'method' => __FUNCTION__,
            'result' => $rs,
            'exec_time' => round(microtime(true) - $startTime, 3)
        ];
        $logData = array_merge($logData, $params);
        Log::info('ELASTIC', $logData);*/
        return $rs;
    }

    public function deleteByQuery($query)
    {
        if (!$this->existsIndex()) {
            return false;
        }
        $params = [
            'index' => $this->indexName,
            'routing' => $this->routing,
            'body' => $query,
        ];
        //$startTime = microtime(true);
        $rs = $this->esClient->deleteByQuery($params);
        /*$logData = [
            'method' => __FUNCTION__,
            'result' => $rs,
            'exec_time' => round(microtime(true) - $startTime, 3)
        ];
        $logData = array_merge($logData, $params);
        Log::info('ELASTIC', $logData);*/
        return $rs;
    }

    public function setLimit($offset = 0, $limit = 20)
    {
        if (!is_numeric($offset) || !is_numeric($limit)) {
            return false;
        }
        $this->offset = $offset;
        $this->limit = $limit;
        return $this;
    }

    /**
     * 设置 routing
     * @param $routingField
     * @return $this
     */
    public function setRouting($routingField)
    {
        if ($routingField) {
            $sRoutingField = $routingField;
            if (is_array($routingField)) {
                $sRoutingField = implode(',', $routingField);
            }
            $this->routing = $sRoutingField;
        }
        return $this;
    }

    public function setOrder($order)
    {
        if (!empty($order) && is_array($order)) {
            $this->order = [];
            foreach ($order as $key => $value) {
                $this->order[] = [$key => ['order' => $value]];
            }
        }
        return $this;
    }

    public function setMinScore($score)
    {
        if (!is_numeric($score)) {
            return false;
        }
        $this->score = $score;
        return $this;
    }

    public function resetParams()
    {
        $this->setLimit(self::DEFAULT_OFFSET, self::DEFAULT_LIMIT);
        $this->setMinScore(self::DEFAULT_SCORE);
        $this->setOrder([self::DEFAULT_ORDER => 'desc']);
        $this->aggs = [];
    }


    /**
     * replace dsl aggs segment
     * @param array $aggs
     * @return $this
     */
    public function setAggs(array $aggs)
    {
        $this->aggs = $aggs;
        return $this;
    }

    /**
     * 查询
     * @param array $query
     * @param array $fields
     * @return array|bool
     */
    public function search($query = [], $fields = [])
    {
        if (!$this->existsIndex()) {
            return false;
        }

        $body = [
            'from' => $this->offset,
            'size' => $this->limit,
        ];

        if (empty($query) && !is_array($query)) {
            $body['query']['match_all'] = new \stdClass();
        } else {
            $body['query'] = $query;
        }

        if ($this->aggs) {
            $body['aggs'] = $this->aggs;
        }

        if (!empty($fields)) {
            $body['_source'] = $fields;
        }

        if ($this->order) {
            $body['sort'] = $this->order;
        }

        $params = [
            'index' => $this->indexName,
            'body' => $body,
        ];
        if ($this->routing) {
            $params['routing'] = $this->routing;
        }

       // $startTime = microtime(true);

        $rs = $this->esClient->search($params);
        $this->resetParams();
        /*$logData = [
            'method' => __FUNCTION__,
            'result' => $rs,
            'exec_time' => round(microtime(true) - $startTime, 3)
        ];
        $logData = array_merge($logData, $params);
        Log::info('ELASTIC', $logData);*/
        return $this->__formatSearchHits($rs);
    }


    /**
     *  批量获取信息。
     * @return array
     */
    public function mSearch(): array
    {
        $multiBody = self::$multiBody;
        $params['body'] = $multiBody;
        $multiRs = $this->esClient->msearch($params);
        $ret = [];
        // todo $ret 结果返回问题
        foreach ($multiRs['responses'] as $k => $rs) {
            $ret = $this->__formatSearchHits($rs);
        }
        return $ret;
    }

    /**
     * 多个 index 实现 mSearch
     *
     * @param array $params
     * @return array
     *
     * @example
     * $params = [
     *      'body' => [
     *          [
     *              'index' => 'ads_jzc_hot_selling_chapter_hw_class_ds',
     *              'type' => '_doc'
     *          ],
     *          [
     *              'query' => [
     *                  'terms' => ['chapter_id' => [10310486321]],
     *              ],
     *          ],
     *          [
     *              'index' => 'ads_jzc_hot_selling_chapter_hw_corp_ds',
     *              'type' => '_doc'
     *          ],
     *          [
     *              'query' => [
     *                  'terms' => ['chapter_id' => [10310486321, 10310486322]],
     *              ],
     *          ],
     *      ]
     * ];
     */
    public function moreIndexMSearch(array $params): array
    {
        $multiRs = $this->esClient->msearch($params);
        $ret = [];
        foreach ($multiRs['responses'] as $k => $rs) {
            $ret[$k] = $rs['hits']['hits'] ?? [];
        }
        return $ret;
    }

    /**
     * @param array $query
     * @return array
     */
    public function buildMultiBody(array $query)
    {
        $onoBody = self::$multiBody;
        $indexInfo = [
            'index' => $this->indexName
        ];
        $onoBody[] = $indexInfo;
        $onoBody[] = $query;
        self::$multiBody = $onoBody;
    }


    /**
     * 获取multiBody
     * @return array
     */
    public function getMultiBody()
    {
        return self::$multiBody;
    }

    /**
     *
     */
    public function resetMultiBody()
    {
        self::$multiBody = [];
        return true;
    }

    public function setRefresh()
    {
        if (!$this->existsIndex()) {
            return false;
        }

        $params = [
            'index' => $this->indexName,
        ];

        return $this->esClient->indices()->refresh($params);

    }

    /**
     * 格式化 es 的查询结果
     * @param $rs
     * @return array
     */
    private function __formatSearchHits(array $rs): array
    {
        $rows = [];
        foreach ($rs['hits']['hits'] as $hit) {
            $hit['_source']['_id'] = $hit['_id'];
            $rows[] = $hit['_source'];
        }
        if (is_array($rs['hits']['total']) && isset($rs['hits']['total']['value'])) {
            $total = (int)$rs['hits']['total']['value'];
        } else {
            $total = (int)$rs['hits']['total'];
        }
        // 聚合信息的时候值只返回聚合数据就行。非聚合原样返回。
        if (isset($rs["aggregations"]) && !empty($rs["aggregations"])) {
            $rows = $rs['aggregations'];
        }
        return ['total' => $total, 'items' => $rows];
    }

    //销毁
    public function __destruct()
    {
        unset($this->esClient);
    }
}
