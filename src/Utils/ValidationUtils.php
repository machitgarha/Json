<?php

namespace MAChitgarha\Json\Utils;

use MAChitgarha\Json\Exceptions\RuntimeException;

/**
 * General utilies for providers.
 */
class ValidationUtils
{
    /**
     * Ensures a class exists.
     *
     * @param string $class The class name to be checked.
     * @param string $classType The type of class, i.e., the group name in which the
     * passed class counts of. Used to generate the exception message.
     * @throws RuntimeException When the class does not exist.
     */
    public static function ensureClassExists(
        string $class,
        string $classType = null
    ): void {
        if (\class_exists($class)) {
            throw new RuntimeException(ucfirst(
                ($classType ? "$classType " : "") . "class '$class' does not exist"
            ));
        }
    }

    /**
     * Ensures a class implements a specific interfaces.
     *
     * @param string $class The class name to be checked.
     * @param string $interfaces The interface to be implemented.
     * @throws RuntimeException When the class does not implement the interface.
     */
    public static function ensureImplements(
        string $class,
        string $interface
    ): void {
        if (!(new \ReflectionClass($class))->implementsInterface($interface)) {
            throw new RuntimeException(
                "Class '$class' must implement '$interface' interface"
            );
        }
    }
}
