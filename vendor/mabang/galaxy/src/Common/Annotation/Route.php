<?php

namespace Mabang\Galaxy\Common\Annotation;

/**
 *
 * Annotation表示该类是一个注解类 Target表示注解生效的范围(ALL,CLASS,METHOD,PROPERTY,ANNOTATION)
 *
 * @Annotation
 * @Target({"METHOD","CLASS"})
 */
final class Route
{
    /**
     * @Required()
     * @var string
     */
    public $route;

    /**
     * @Enum({"POST", "GET", "PUT", "DELETE"})
     * @var string
     */
    public $method;

    /**
     * @Enum({"JSON", "FORM", "BINARY","QUERY","REGEX"})
     * @var string
     */
    public $contextType;

    /**
     * @var mixed
     */
    public $param;

    
}