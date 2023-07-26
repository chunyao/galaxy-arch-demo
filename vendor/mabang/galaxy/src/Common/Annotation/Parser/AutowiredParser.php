<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace Swoft\Bean\Annotation\Parser;

use PhpDocReader\AnnotationException;
use PhpDocReader\PhpDocReader;
use ReflectionException;
use ReflectionProperty;



class AutowiredParser 
{
    /**
     * Parse annotation
     *
     * @param int    $type
     * @param Inject $annotationObject
     *
     * @return array
     * @throws BeanException
     * @throws AnnotationException
     * @throws ReflectionException
     */
    public function parse($annotationObject): array
    {
        

        $inject = $annotationObject->getName();
        if (!empty($inject)) {
            return [$inject, true];
        }

        // Parse php document
        $phpReader       = new PhpDocReader();
        $reflectProperty = new ReflectionProperty($this->className, $this->propertyName);
        $docInject       = $phpReader->getPropertyClass($reflectProperty);
        if (empty($docInject)) {
            throw new BeanException('`@Autowired` must be define inejct value or `@var type` ');
        }

        return [$docInject, true];
    }
}
