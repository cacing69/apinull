<?php

namespace App\Kernel;

class Container
{
    protected $instances = [];

    public function bind($abstract, $concrete)
    {
        $this->instances[$abstract] = $concrete;
    }

    public function make($abstract)
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $reflectionClass = new \ReflectionClass($abstract);

        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception("Class {$abstract} cannot be instantiated.");
        }

        $constructor = $reflectionClass->getConstructor();

        if (is_null($constructor)) {
            return new $abstract;
        }

        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);

        $object = $reflectionClass->newInstanceArgs($dependencies);
        $this->instances[$abstract] = $object;

        return $object;
    }

    protected function resolveDependencies($parameters)
    {
        $dependencies = [];

        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve dependency {$parameter->name}");
                }
            } else {
                $dependencies[] = $this->make($dependency->name);
            }
        }

        return $dependencies;
    }
}
