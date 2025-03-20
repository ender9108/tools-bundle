<?php

namespace EnderLab\ToolsBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use EnderLab\DddBundle\ApiPlatform\ApiResourceInterface;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

class MappingArgs
{
    /**
     * @throws ReflectionException
     */
    public function generate(string $className, array $args): array
    {
        $reflectionClass = new ReflectionClass($className);
        $properties = $reflectionClass->getProperties();
        $preparedArguments = [];

        foreach ($properties as $property) {
            if (array_key_exists($property->getName(), $args)) {
                if (null === $args[$property->getName()] && false === $property->getType()->allowsNull()) {
                    throw new InvalidArgumentException('You must specify a value for ' . $property->getName());
                }

                $propertyType = $property->getType()->getName();

                switch ($propertyType) {
                    case 'string':
                    case 'int':
                    case 'float':
                    case 'bool':
                        $preparedArguments[$property->getName()] = $args[$property->getName()] ?? null;
                        break;
                    case 'array':
                        $preparedArguments[$property->getName()] = null === $args[$property->getName()] ? [] : [$args[$property->getName()]];
                        break;
                    case 'DateTime':
                    case 'DateTimeInterface':
                    case 'DateTimeImmutable':
                        $propertyType = '\\'.($propertyType === 'DateTimeInterface' ? 'DateTime' : $propertyType);
                        $preparedArguments[$property->getName()] = null === $args[$property->getName()] ? null : new $propertyType($args[$property->getName()]);
                        break;
                }
            }
        }

        return $preparedArguments;
    }

    /**
     * @throws ReflectionException
     */
    public function generateArgsCommandFromResource(string $commandClassName, ApiResourceInterface $apiResource): array
    {
        $reflectionClass = new ReflectionClass($commandClassName);
        $properties = $reflectionClass->getProperties();

        $reflectionClassResource = new ReflectionClass($apiResource);
        $resourceProperties = new ArrayCollection($reflectionClassResource->getProperties());
        $preparedArguments = [];

        foreach ($properties as $property) {
            $resourceProperty = $resourceProperties->filter(function (ReflectionProperty $resourceProperty) use ($property) {
                return $resourceProperty->getName() === $property->getName();
            })->first();

            if (false !== $resourceProperty) {
                $value = $reflectionClassResource->getProperty($resourceProperty->getName())->getValue($apiResource);

                /*
                 * @todo check bug property reflection getDefaultValue
                if (
                    null === $value &&
                    false === $property->getType()->allowsNull() &&
                    false === $property->hasDefaultValue()
                ) {
                    throw new InvalidArgumentException('You must specify a value for ' . $property->getName());
                }*/

                $propertyType = $property->getType()->getName();

                switch ($propertyType) {
                    case 'string':
                    case 'int':
                    case 'float':
                    case 'bool':
                        $preparedArguments[$property->getName()] = $value;
                        break;
                    case 'array':
                        $preparedArguments[$property->getName()] =
                            null === $value ?
                            [] :
                            (is_array($value) ? $value : [$value])
                        ;
                        break;
                    case 'DateTime':
                    case 'DateTimeInterface':
                    case 'DateTimeImmutable':
                        $propertyType = '\\'.($propertyType === 'DateTimeInterface' ? 'DateTime' : $propertyType);
                        $preparedArguments[$property->getName()] = null === $value ? null : new $propertyType($value);
                        break;
                }
            }
        }

        return $preparedArguments;
    }
}
