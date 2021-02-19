<?php

if (!defined('RUNKIT7_ACC_PUBLIC')) {
    // runkit7 stub based on "API for runkit7 4.0.0"
    // only used (by phpstan) values are left
    // see full API at https://github.com/runkit7/runkit7/blob/master/runkit-api.php

    define('RUNKIT7_ACC_PUBLIC', 0x100);
    define('RUNKIT7_ACC_PROTECTED', 0x200);
    define('RUNKIT7_ACC_PRIVATE', 0x400);
    define('RUNKIT7_ACC_STATIC', 0x1);


    /**
     * Similar to define(), but allows defining in class definitions as well.
     *
     * NOTE: Constants and class constants within the same file may be inlined by the Zend VM optimizer,
     * and this may or may not have an effect if the constant already exists.
     *
     * Aliases: runkit_constant_add
     *
     * @param string $constName  Name of constant to declare. Either a string to indicate a global constant, or
     *                           className::constName to indicate a class constant.
     * @param mixed  $value      null, Bool, Long, Double, String, Resource, or Array value to store in the
     *                           new constant.
     * @param int    $visibility Visibility of the constant. Public by default.
     *
     * @return bool - TRUE on success or FALSE on failure.
     */
    function runkit7_constant_add(string $constName, $value, int $visibility = RUNKIT7_ACC_PUBLIC): bool
    {
        return false;
    }

    /**
     * Similar to define(), but allows defining in class definitions as well.
     *
     * NOTE: Constants and class constants within the same file may be inlined by the Zend VM optimizer,
     * and this may or may not have an effect if the constant already exists.
     *
     * Aliases: runkit_constant_redefine
     *
     * @param string   $constName     Name of constant to declare. Either a string to indicate a global constant, or
     *                                className::constName to indicate a class constant.
     * @param mixed    $value         null, Bool, Long, Double, String, Resource, or Array value to store in the new
     *                                constant.
     * @param int|null $newVisibility The new visibility of the constant. Unchanged by default.
     *
     * @return bool - TRUE on success or FALSE on failure.
     */
    function runkit7_constant_redefine(string $constName, $value, int $newVisibility = null): bool
    {
        return false;
    }

    /**
     * Remove/Delete an already defined constant
     *
     * NOTE: Constants and class constants within the same file may be inlined by the Zend VM optimizer,
     * and this may or may not have an effect if the constant already exists.
     *
     * Aliases: runkit_constant_remove
     *
     * @param string $constName Name of constant to declare. Either a string to indicate a global constant, or
     *                          className::constName to indicate a class constant.
     *
     * @return bool - TRUE on success or FALSE on failure.
     */
    function runkit7_constant_remove(string $constName): bool
    {
        return false;
    }

    /**
     * Dynamically adds a new method to a given class, similar to create_function()
     * (Signature 2 of 2)
     *
     * Aliases: runkit_method_add
     *
     * @param string  $className   The class to which this method will be added
     * @param string  $methodName  The name of the method to add
     * @param Closure $closure     A closure to use as the source for this function. Static variables and `use`
     *                             variables and return types are copied.
     * @param int     $flags       The type of method to create, can be RUNKIT7_ACC_PUBLIC, RUNKIT7_ACC_PROTECTED or
     *                             RUNKIT7_ACC_PRIVATE optionally combined via bitwise OR with RUNKIT7_ACC_STATIC (since
     *                             1.0.1)
     * @param ?string $doc_comment The doc comment of the method
     * @param ?bool   $is_strict   Set to true to make the redefined function use strict types.
     *
     * @return bool - True on success or false on failure.
     */
    function runkit7_method_add(
        string $className,
        string $methodName,
        Closure $closure,
        int $flags = RUNKIT7_ACC_PUBLIC,
        string $doc_comment = null,
        bool $is_strict = null
    ): bool {
        return false;
    }

    /**
     * Dynamically removes the given method
     * (Signature 2 of 2)
     *
     * Aliases: runkit_method_rename
     *
     * @param string $className  The class in which to remove the method
     * @param string $methodName The name of the method to remove
     *
     * @return bool - True on success or false on failure.
     */
    function runkit7_method_remove(string $className, string $methodName): bool
    {
        return false;
    }

    /**
     * Dynamically changes the name of the given method
     *
     * Aliases: runkit_method_rename
     *
     * @param string $classname  The class in which to rename the method
     * @param string $methodname The name of the method to rename
     * @param string $newname    The new name to give to the renamed method
     *
     * @return bool - True on success or false on failure.
     */
    function runkit7_method_rename(string $classname, string $methodname, string $newname): bool
    {
        return false;
    }
}
