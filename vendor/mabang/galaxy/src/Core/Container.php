<?php

namespace Mabang\Galaxy\Core;


use Mabang\Galaxy\Common\Annotation\Autowired;
use PhpDocReader\PhpDocReader;
use Psr\Container\ContainerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use function PHPUnit\Framework\isInstanceOf;
use \ReflectionClass;

class Container implements ContainerInterface
{
    /**
     * 注册单例
     *
     * @var array
     */
    protected array $singletonPool;

    /**
     * @var \Swoft\Bean\Container
     */

    public function init(): void
    {
        // Parse annotations
        $this->parseOneClassAnnotation();


    }

    private function parseOneClassAnnotation($reflectionClass): array
    {
        // Annotation reader
        $reader = new AnnotationReader();
        $phpReader = new PhpDocReader();
        $className = $reflectionClass->getName();

        $oneClassAnnotation = [];
        $classAnnotations = $reader->getClassAnnotations($reflectionClass);

        // Register annotation parser
//        foreach ($classAnnotations as $classAnnotation) {
//            if ($classAnnotation instanceof AnnotationParser) {
//             //   $this->registerParser($className, $classAnnotation);
//                return [];
//            }
//        }

        // Class annotation
        if (!empty($classAnnotations)) {
            $oneClassAnnotation['annotation'] = $classAnnotations;
            $oneClassAnnotation['reflection'] = $reflectionClass;
        }

        // Property annotation
        $reflectionProperties = $reflectionClass->getProperties();
        foreach ($reflectionProperties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $propertyAnnotations = $reader->getPropertyAnnotations($reflectionProperty);

            if (!empty($propertyAnnotations)) {
                $oneClassAnnotation['properties'][$propertyName]['annotation'] = $propertyAnnotations;
                $oneClassAnnotation['properties'][$propertyName]['reflection'] = $reflectionProperty;
                $reflectProperty = new \ReflectionProperty($reflectionProperty->class, $reflectionProperty->name);
                $docInject = $phpReader->getPropertyClass($reflectProperty);
                $oneClassAnnotation['properties'][$propertyName]['reflection']->docInject = $docInject;

            }
        }

        // Method annotation
        $reflectionMethods = $reflectionClass->getMethods();
        foreach ($reflectionMethods as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            $methodAnnotations = $reader->getMethodAnnotations($reflectionMethod);

            if (!empty($methodAnnotations)) {
                $oneClassAnnotation['methods'][$methodName]['annotation'] = $methodAnnotations;
                $oneClassAnnotation['methods'][$methodName]['reflection'] = $reflectionMethod;
            }
        }

        $parentReflectionClass = $reflectionClass->getParentClass();
        if ($parentReflectionClass !== false) {
            $parentClassAnnotation = $this->parseOneClassAnnotation($parentReflectionClass);
            if (!empty($parentClassAnnotation)) {
                $oneClassAnnotation['parent'] = $parentClassAnnotation;
            }
        }

        //   var_dump($oneClassAnnotation);
        return $oneClassAnnotation;
    }

    /**
     * @throws ReflectionException
     */
    public function get(string $class, string $id = '0')
    {
        // 构造函数参数
        $constructorParams = [];
        // 判断当前类是否已注册
        if (isset($this->singletonPool[$id][$class])) {
            return $this->singletonPool[$id][$class];
        }
        // 反射类
        $reflector = new \ReflectionClass($class);
        // 是否需要处理构造函数
        if ($reflector->getConstructor()) {
            // 构造函数参数
            $params = $reflector->getConstructor()->getParameters();
            if (!empty($params)) {
                foreach ($params as $param) {
                    // 反射参数类
                    try {
                        $paramReflector = new ReflectionClass($param->getClass()->name);
                        // 参数类名
                        $paramClass = $paramReflector->name;
                        // 递归实例化参数
                        $constructorParams[] = $this->get($paramClass);
                    }catch (\Throwable $e){
                        
                    }


                }
            }
        }

        if (!empty($constructorParams)) {
            return new $class(...$constructorParams);
        }
        $object = new $class();
        if ($reflector->getProperties()) {
            $i = 0;
            foreach ($this->parseOneClassAnnotation($reflector)['properties'] as $key => $obj) {

                if ($key === $obj['reflection']->name && $this->hasAnnotation($obj['annotation'],Autowired::class)){
                    echo '注入: ' . $key . '  ' . $obj['reflection']->name . PHP_EOL;
                    $container = new Container();
                    $subObj = $container->get($obj['reflection']->docInject);
                    $attr = $reflector->getProperty($key);
                    $attr->setAccessible(true);
                    $attr->setValue($object, $subObj);

                }

                $i++;
            }

        }
        // 更新注册树
        $this->singletonPool[$id][$class] = $object;
        return $object;
    }

    private function hasAnnotation(array $array, $obj): bool
    {
        foreach ($array as $item) {
            try {
                if ($item instanceof $obj) {
                    return true;
                }
            }catch (\Throwable $e){

            }

        }
        return false;
    }

    /**
     * @param string $class
     * @return bool
     */
    public function has(string $class, string $id = '0'): bool
    {
        return isset($this->singletonPool[$id][$class]);
    }
}


