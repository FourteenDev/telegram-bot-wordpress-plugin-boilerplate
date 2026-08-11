<?php

namespace TelegramPluginBoilerplate;

/**
 * Dependency Injection container for managing class dependencies and instances.
 *
 * This container implements a simple dependency injection container that allows binding abstractions to concrete implementations and resolving dependencies automatically.
 */
class Container
{
	/**
	 * Registered bindings.
	 *
	 * @var	array<string, array{concrete: callable|string, shared: bool}>
	 */
	protected $bindings = [];

	/**
	 * Singleton instances.
	 *
	 * @var	array<string, object>
	 */
	protected $instances = [];

	/**
	 * Binds a class or interface to a concrete implementation.
	 *
	 * @param	string			$abstract	Abstract class or interface name.
	 * @param	callable|string	$concrete	Concrete implementation or factory closure.
	 * @param	bool			$shared		Whether the binding should be shared (singleton).
	 *
	 * @return 	void
	 *
	 * @throws	\Exception					When invalid concrete implementation provided.
	 */
	public function bind($abstract, $concrete = null, $shared = false): void
	{
		$concrete = $concrete ?? $abstract;

		if (!is_string($concrete) && !$concrete instanceof \Closure)
			throw new \Exception('Concrete implementation must be a class name or closure');

		$this->bindings[$abstract] = compact('concrete', 'shared');
	}

	/**
	 * Binds a class as a singleton instance.
	 *
	 * @param	string			$abstract	Abstract class or interface name.
	 * @param	callable|string	$concrete	Concrete implementation or factory closure.
	 *
	 * @return	void
	 */
	public function singleton($abstract, $concrete = null): void
	{
		$this->bind($abstract, $concrete, true);
	}

	/**
	 * Resolves and returns an instance of the requested abstract.
	 *
	 * @param	string	$abstract	Abstract class or interface to resolve.
	 * @param	array	$parameters	Optional parameters to pass to the constructor.
	 *
	 * @return	object				Resolved instance.
	 *
	 * @throws	\Exception 			When concrete cannot be resolved or class reflection fails.
	 */
	public function make($abstract, $parameters = []): object
	{
		if (isset($this->instances[$abstract]))
			return $this->instances[$abstract];

		$concrete = $this->bindings[$abstract]['concrete'] ?? $abstract;
		$object   = $concrete instanceof \Closure ? $concrete($this, $parameters) : $this->build($concrete, $parameters);

		if (($this->bindings[$abstract]['shared'] ?? false) && is_object($object))
			$this->instances[$abstract] = $object;

		return $object;
	}

	/**
	 * Instantiates a concrete class with its dependencies using reflection.
	 *
	 * @param	string					$concrete	Class name to instantiate.
	 * @param	array<string, mixed>	$parameters	Optional parameters to pass to the constructor.
	 *
	 * @return	object					Instantiated object.
	 *
	 * @throws	\Exception				When Class is not found or dependencies cannot be resolved.
	 */
	protected function build(string $concrete, array $parameters): object
	{
		try {
			$reflector = new \ReflectionClass($concrete);
		} catch (\ReflectionException $e) {
			throw new \Exception("Class {$concrete} not found");
		}

		if (!$constructor = $reflector->getConstructor())
			return $reflector->newInstance();

		return $reflector->newInstanceArgs($this->resolveDependencies($constructor->getParameters(), $parameters));
	}

	/**
	 * Resolves method/constructor dependencies.
	 *
	 * @param	\ReflectionParameter[]	$dependencies	Constructor parameters to resolve.
	 * @param	array<string, mixed>	$parameters		Optional parameters to use for resolution.
	 *
	 * @return	array<mixed>							Resolved dependencies.
	 *
	 * @throws	\Exception								When a dependency cannot be resolved.
	 */
	protected function resolveDependencies(array $dependencies, array $parameters): array
	{
		$results = [];

		foreach ($dependencies as $dependency)
		{
			$name = $dependency->getName();

			if (array_key_exists($name, $parameters))
			{
				$results[] = $parameters[$name];
				continue;
			}

			if ($type = $dependency->getType())
			{
				$typeName = $type->getName();

				if ($type instanceof \ReflectionNamedType && !$type->isBuiltin())
				{
					$results[] = $this->make($typeName);
					continue;
				}
			}

			if ($dependency->isDefaultValueAvailable())
			{
				$results[] = $dependency->getDefaultValue();
				continue;
			}

			throw new \Exception("Cannot resolve dependency '{$name}' for {$dependency->getDeclaringClass()?->getName()}");
		}

		return $results;
	}
}
