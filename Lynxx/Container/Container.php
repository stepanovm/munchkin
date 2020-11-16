<?php


namespace Lynxx\Container;


use Psr\Container\ContainerInterface;

class Container implements ContainerInterface
{
    private $singltones = [];
    private $definitions = [];

    public function __construct()
    {
        $this->definitions = require __DIR__ . '/../../app/config/dependencies.php';
    }

    public function get($id)
    {
        if (array_key_exists($id, $this->singltones)) {
            return $this->singltones[$id];
        }

        if (array_key_exists($id, $this->definitions)) {
            $definition = $this->definitions[$id];
            if ($definition instanceof \Closure) {
                $this->singltones[$id] = $definition($this);
            } else {
                $this->singltones[$id] = $definition;
            }

        } else if (class_exists($id)) {

            $reflection = new \ReflectionClass($id);
            $arguments = [];

            if (($constructor = $reflection->getConstructor()) !== null) {

                foreach ($constructor->getParameters() as $parameter) {
                    if ($paramClass = $parameter->getClass()) {
                        $arguments[] = $this->get($paramClass->getName());
                    } else if ($parameter->isArray()) {
                        $arguments[] = [];
                    } else if ($parameter->isDefaultValueAvailable()) {
                        $arguments[] = $parameter->getDefaultValue();
                    } else {
                        throw new ContainerException('parse argument error');
                    }
                }
            }

            $this->singltones[$id] = new $id(...$arguments);

        } else {
            throw new ServiceNotFoundException('service ' . $id . ' not found');
        }

        return $this->singltones[$id];
    }

    public function set($id, $value)
    {
        if (array_key_exists($id, $this->definitions)) {
            throw new \InvalidArgumentException('service ' . $id . ' already exist');
        }
        $this->definitions[$id] = $value;
    }

    public function has($id)
    {
        // TODO: Implement has() method.
    }


}