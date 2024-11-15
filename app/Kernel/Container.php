<?php

namespace App\Kernel;

/**
 * Class Container
 *
 * A simple Dependency Injection (DI) container that resolves and instantiates classes and their dependencies.
 * This class uses reflection to automatically resolve class dependencies and instantiate the required objects.
 * It also supports caching of resolved instances to avoid redundant instantiations.
 *
 * @package App\Kernel
 */
class Container
{
    /**
     * @var array $instances
     * A cache to store resolved instances, allowing for reuse of the same object.
     */
    protected $instances = [];

    /**
     * Bind an abstract class or interface to a concrete implementation.
     *
     * This method allows you to register an interface or abstract class with its corresponding concrete class,
     * which the container can then resolve and instantiate when requested.
     *
     * @param string $abstract The abstract class or interface name.
     * @param string $concrete The concrete class name that implements or extends the abstract class.
     */
    public function bind($abstract, $concrete)
    {
        // Store the concrete class for the abstract class or interface in the instances array
        $this->instances[$abstract] = $concrete;
    }

    /**
     * Resolve and instantiate a class or interface with its dependencies.
     *
     * This method will check if the requested class has already been instantiated and cached.
     * If not, it uses reflection to inspect the class constructor and resolve its dependencies.
     *
     * @param string $abstract The class or interface name to resolve.
     * @return object The instantiated class object with all dependencies resolved.
     * @throws \Exception If the class cannot be instantiated or its dependencies cannot be resolved.
     */
    public function make($abstract)
    {
        // Check if the instance is already cached
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        // Use reflection to inspect the class
        $reflectionClass = new \ReflectionClass($abstract);

        // Check if the class is instantiable
        if (!$reflectionClass->isInstantiable()) {
            throw new \Exception("Class {$abstract} cannot be instantiated.");
        }

        // Get the constructor of the class
        $constructor = $reflectionClass->getConstructor();

        // If the class has no constructor, instantiate it without dependencies
        if (is_null($constructor)) {
            return new $abstract;
        }

        // Get the constructor's parameters
        $parameters = $constructor->getParameters();

        // Resolve the constructor's dependencies
        $dependencies = $this->resolveDependencies($parameters);

        // Instantiate the class with the resolved dependencies
        $object = $reflectionClass->newInstanceArgs($dependencies);

        // Cache the resolved instance for future use
        $this->instances[$abstract] = $object;

        return $object;
    }

    /**
     * Resolve the dependencies for the given constructor parameters.
     *
     * This method will iterate over the constructor's parameters and resolve their dependencies by
     * recursively calling `make()` for class-type parameters. Non-class parameters will either be given
     * default values or throw an exception if no default is available.
     *
     * @param \ReflectionParameter[] $parameters An array of parameters from the class constructor.
     * @return array An array of resolved dependencies.
     * @throws \Exception If a dependency cannot be resolved.
     */
    protected function resolveDependencies($parameters)
    {
        $dependencies = [];

        // Iterate through each parameter to resolve its dependency
        foreach ($parameters as $parameter) {
            $dependency = $parameter->getClass();

            // If the parameter has no class type, check for a default value or throw an exception
            if ($dependency === null) {
                if ($parameter->isDefaultValueAvailable()) {
                    // Use default value if available
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    // Throw exception if the dependency cannot be resolved
                    throw new \Exception("Cannot resolve dependency {$parameter->name}");
                }
            } else {
                // Resolve the class dependency recursively
                $dependencies[] = $this->make($dependency->name);
            }
        }

        return $dependencies;
    }
}
