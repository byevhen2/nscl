<?php

declare(strict_types = 1);

/**
 * @param mixed $var
 * @return string The type of variable.
 */
function typeof($var): string
{
    $type = strtolower(gettype($var));

    // Generalize or change the name of the type
    switch ($type) {
        case 'boolean': $type = 'bool';  break;
        case 'integer': $type = 'int';   break;
        case 'double':  $type = 'float'; break;

        case 'array':
            if (is_callable($var)) { // [%Object or class%, %Method name%]
                $type = 'callback';
            }
            break;

        case 'object':
            if (is_callable($var)) {
                $type = 'closure';
            } else if ($var instanceof \DateTime) {
                $type = 'date';
            } else if (class_exists('\NSCL\ToStr\AsIs') && $var instanceof \NSCL\ToStr\AsIs) {
                $type = 'asis';
            }
            break;

        case 'callable':
            if (is_string($var)) {
                $type = 'function';
            } else {
                $type = 'closure';
            }
            break;
    }

    return $type;
}
