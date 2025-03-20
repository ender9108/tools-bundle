<?php

namespace EnderLab\ToolsBundle\Service;

use ReflectionClass;

trait ListTrait
{
    private static array $cache = [];

    public static function getConstantsList(): array
    {
        if (!empty(self::$cache)) {
            return self::$cache;
        }

        $class = new ReflectionClass(__CLASS__);
        $constants = $class->getConstants();

        foreach ($constants as $constant) {
            self::$cache[$constant] = $constant;
        }

        return self::$cache;
    }
}
