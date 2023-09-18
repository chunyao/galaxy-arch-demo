<?php
class Single
{
    public static $instance = [];

    /**
     * 单利模式
     * @return static
     */
    public static function  me()
    {
        $className = get_called_class();
        isset(self::$instance[$className]) || self::$instance[$className] = new static();
        return self::$instance[$className];
    }

}
class Entity extends Single {

    public string $public;

    /**
     * @return string
     */
    public function getPublic(): string
    {
        return $this->public;
    }

    /**
     * @param string $public
     */
    public function setPublic(string $public): void
    {
        $this->public = $public;
    }

    /**
     * @return string
     */
    public function getPrivate(): string
    {
        return $this->private;
    }

    /**
     * @param string $private
     */
    public function setPrivate(string $private): void
    {
        $this->private = $private;
    }

    /**
     * @return string
     */
    public function getProtected(): string
    {
        return $this->protected;
    }

    /**
     * @param string $protected
     */
    public function setProtected(string $protected): void
    {
        $this->protected = $protected;
    }
    private string $private;
    protected string $protected;

    private static Entity $entity;

    public static function setEntity(Entity $entity): void
    {
        self::$entity = $entity;
    }


}

/*test*/
/*第一次执行*/
$entity = Entity::me();
Entity::me()->setProtected("Protected1");
Entity::me()->setPrivate("Private1");
Entity::me()->setPublic("Public1");


Entity::me()->setEntity($entity);
print_r($entity);
unset($entity);
/*第二次执行*/


$entity=Entity::me();
Entity::me()->setProtected("Protected2");
Entity::me()->setPrivate("Private2");


print_r($entity);
/* 会发现 unset 并没有生效，这种写法对单例没有效果*/

$entity = new Entity();
$entity ->setProtected("Protected1");
$entity ->setPrivate("Private1");
$entity ->setPublic("Public1");
$entity ->setEntity($entity);
print_r($entity);

$entity = new Entity();
$entity ->setProtected("Protected1");
Entity::me()->setProtected("Protected2");
Entity::me()->setPrivate("Private2");
print_r($entity);
$url = uniqid()."://www.".uniqid().".".uniqid();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);

curl_exec($ch);
var_dump(curl_error($ch));
var_dump(curl_errno($ch));
curl_close($ch);
