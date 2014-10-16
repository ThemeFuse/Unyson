<?php if (!defined('FW')) die('Forbidden');

/**
 * Class FW_Backup_Callable
 *
 * Designed for the cases when callable have one or more first
 * arguments about which those who should call it knows nothing.
 *
 * Example
 * =======
 *
 * $callable = FW_Backup_Callable::make(array($this, 'hello'), $foo, $bar)
 *     This will wrap $this->hello which should have at least two
 *     arguments into a callable with predefined first two arguments.
 *
 * call_user_func($callable, $aa, $bb, $cc, ...)
 *     This is the same as $this->hello($foo, $bar, $aa, $bb, $cc)
 */
class FW_Backup_Callable
{
    private $callable;
    private $first_args;

    public function __construct($callable, $first_args)
    {
        $this->callable = $callable;
        $this->first_args = $first_args;
    }

    public function forward()
    {
        $second_args = func_get_args();
        return call_user_func_array($this->callable, array_merge($this->first_args, $second_args));
    }

    static public function make($callable)
    {
        $first_args = func_get_args();
        array_shift($first_args);

        return array(new self($callable, $first_args), 'forward');
    }
}
