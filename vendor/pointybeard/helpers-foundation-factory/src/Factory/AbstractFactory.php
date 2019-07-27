<?php

declare(strict_types=1);

namespace pointybeard\Helpers\Foundation\Factory;

abstract class AbstractFactory implements Interfaces\FactoryInterface
{
    public static function build(string $name, ...$arguments): object
    {
        $factory = new static();
        $concreteClass = $factory->instanciate(
            $factory->generateTargetClassName($name),
            ...$arguments
        );

        return $concreteClass;
    }

    public function getExpectedClassType(): ?string
    {
        // side effect: returning null will cause $this->isExpectedClassType()
        // to always return true.
        return null;
    }

    protected function generateTargetClassName(string ...$args): string
    {
        // If the number of items in $args is not equal to the
        // number of directives in static::getTemplateNamespace(), an E_WARNING
        // will be thrown when vsprintf() is called. We need to trap this
        // warning and throw it as an exception instead, hence the use of
        // set_error_handler() here.
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
        }, E_WARNING);

        try {
            $result = vsprintf($this->getTemplateNamespace(), $args);
        } catch (\ErrorException $ex) {
            restore_error_handler();
            throw new Exceptions\UnableToGenerateTargetClassNameException(static::class, $ex->getMessage());
        }
        restore_error_handler();

        return $result;
    }

    protected function isExpectedClassType(object $class): bool
    {
        return null === $this->getExpectedClassType() || is_a($class, $this->getExpectedClassType(), false);
    }

    protected function instanciate(string $class, ...$arguments): object
    {
        if (!class_exists($class)) {
            throw new Exceptions\UnableToInstanciateConcreteClassException(sprintf(
                'Class %s does not exist',
                $class
            ));
        }

        $object = empty($arguments)
            ? new $class()
            : new $class(...$arguments)
        ;

        if (!$this->isExpectedClassType($object)) {
            throw new Exceptions\UnableToInstanciateConcreteClassException(sprintf(
                'Class %s is not of expected type %s',
                $class,
                $this->getExpectedClassType()
            ));
        }

        return $object;
    }
}
