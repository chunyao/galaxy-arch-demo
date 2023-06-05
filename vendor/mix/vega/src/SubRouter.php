<?php

namespace Mix\Vega;

/**
 * Class SubRouter
 * @package Mix\Vega
 */
class SubRouter
{

    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var Engine
     */
    protected $engine;

    /**
     * @var \Closure[]
     */
    protected $handlers = [];

    protected $contextType;

    protected $param;

    /**
     * Subrouter constructor.
     * @param string $prefix
     * @param Engine $engine
     */
    public function __construct(string $prefix, Engine $engine)
    {
        $this->prefix = $prefix;
        $this->engine = $engine;

    }

    /**
     * @param string $path
     * @param callable ...$handlers
     * @return Route
     */
    public function handle(string $path, string $contextType, string $param, callable ...$handlers): Route
    {
        return $this->engine->handle($this->prefix . $path, $contextType, $param, ...array_merge($this->handlers, $handlers));
    }

    /**
     * @param string $prefix
     * @return RouterPrefix
     */
    public function pathPrefix(string $prefix): SubRouter
    {
        return new SubRouter($this->prefix . $prefix, $this->engine);
    }

    /**
     * @param \Closure ...$handlers
     * @return Engine
     */
    public function use(\Closure ...$handlers): SubRouter
    {
        $this->handlers = array_merge($this->handlers, $handlers);
        return $this;
    }

    /**
     * @param string $path
     * @param \Closure ...$handlers
     * @return Route
     */
    public function handleFunc(string $path, \Closure ...$handlers): Route
    {
        return $this->engine->handleFunc($this->prefix . $path, ...array_merge($this->handlers, $handlers));
    }

    /**
     * @param string $path
     * @param callable ...$handlers
     * @return Route
     * @deprecated 废弃，请用 handle 替代
     */
    public function handleCall(string $path, callable ...$handlers): Route
    {
        return $this->handle($this->prefix . $path, ...array_merge($this->handlers, $handlers));
    }

}
