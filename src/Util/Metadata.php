<?php

/*
 * This file is part of the PHINT package.
 *
 * (c) Jitendra Adhikari <jiten.adhikary@gmail.com>
 *     <https://github.com/adhocore>
 *
 * Licensed under MIT license.
 */

namespace Ahc\Phint\Util;

use CrazyFactory\DocBlocks\DocBlock;

class Metadata
{
    public function forClass(string $classFqcn): array
    {
        return $this->forReflectionClass(new \ReflectionClass($classFqcn));
    }

    public function forReflectionClass(\ReflectionClass $class): array
    {
        $name  = $class->name;
        $texts = (new DocBlock($class))->texts();
        $title = \array_shift($texts);

        $metadata = [
            'namespace'   => \preg_replace('!\W\w+$!', '', $name),
            'classFqcn'   => $name,
            'classPath'   => $class->getFileName(),
            'name'        => $class->getShortName(),
            'className'   => $class->getShortName(),
            'isTrait'     => $class->isTrait(),
            'isAbstract'  => $class->isAbstract(),
            'isInterface' => $class->isInterface(),
            'newable'     => $class->isInstantiable(),
            'title'       => $title,
            'texts'       => $texts,
            'methods'     => [],
        ];

        foreach ($class->getMethods() as $method) {
            if ($method->class !== $name) {
                continue;
            }

            $metadata['methods'][$method->name] = $this->forReflectionMethod($method);
        }

        return $metadata;
    }

    public function forMethod(string $classFqcn, string $method): array
    {
        $reflMethod = (new \ReflectionClass($classFqcn))->getMethod($method);

        return $this->forReflectionMethod($reflMethod);
    }

    public function forReflectionMethod(\ReflectionMethod $method): array
    {
        $parser = new DocBlock($method);
        $texts  = $parser->texts();
        $title  = \array_shift($texts);

        $metadata = [
            'name'       => $method->name,
            'inClass'    => $method->getDeclaringClass()->name,
            'isStatic'   => $method->isStatic(),
            'isFinal'    => $method->isFinal(),
            'isPublic'   => $method->isPublic(),
            'isAbstract' => $method->isAbstract(),
            'maybeMagic' => \substr($method->name, 0, 2) === '__',
            'title'      => $title,
            'texts'      => $texts,
        ];

        $params = [];
        foreach ($parser->find('param') as $param) {
            if (\preg_match('/(.*)\$(\w+)/', $param->getValue(), $match)) {
                $params[$match[2]] = \trim($match[1]);
            }
        }

        if (null !== $return = $parser->first('return')) {
            $return = \preg_replace('/(\S+)(.*)/', '$1', $return->getValue());
        }

        return $metadata + $this->getMethodParameters($method, $params, $return ?? '');
    }

    protected function getMethodParameters(\ReflectionMethod $method, array $docParams, string $return)
    {
        $params = [];
        $parser = new DocBlock($method);

        foreach ($method->getParameters() as $param) {
            $name = $param->name;
            if (!$param->hasType()) {
                $params[] = \trim(($docParams[$name] ?? '') . " \$$name");

                continue;
            }

            $params[] = $this->getRealType($param) . " \$$name";
        }

        if ($returnType = $method->getReturnType()) {
            $return = $this->getRealType($returnType);
        }

        return \compact('params', 'return');
    }

    protected function getRealType($param): string
    {
        $type = \method_exists($param, 'getType')
            ? $param->getType()
            : (string) $param;

        if (\preg_match('/void|null/', $type)) {
            return $type;
        }

        return $type . ($param->allowsNull() ? '|null' : '');
    }
}
