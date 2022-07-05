<?php

declare(strict_types=1);
/**
 * Parser Reflection API
 *
 * @copyright Copyright 2021, Lisachenko Alexander <lisachenko.it@gmail.com>
 *
 * This source file is subject to the license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Go\ParserReflection;

use ReflectionNamedType as BaseReflectionNamedType;

/**
 * ReflectionNamedType implementation
 */
class ReflectionNamedType extends BaseReflectionNamedType
{
    /**
     * If type allows null or not
     *
     * @var bool
     */
    private $allowsNull;

    /**
     * Is type built-in or not
     *
     * @var
     */
    private $isBuiltin;

    /**
     * @var string Type name
     */
    private $type;

    /**
     * Initializes reflection data
     */
    public function __construct($type, $allowsNull, $isBuiltin)
    {
        $this->type       = $type;
        $this->allowsNull = $allowsNull;
        $this->isBuiltin  = $isBuiltin;
    }

    /**
     * @inheritDoc
     */
    public function allowsNull()
    {
        return $this->allowsNull;
    }

    /**
     * @inheritDoc
     */
    public function isBuiltin()
    {
        return $this->isBuiltin;
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return $this->type;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->type;
    }
}
