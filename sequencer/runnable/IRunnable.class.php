<?php
/**
 * IRunnable interface definition
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * IRunnable defines the interface for runnable that can be chained
 * in @see CommandChain instances.
 * It is merely composed of a run method, taking a variable number
 * of arguments.
 */
interface IRunnable {
    /**
     * Runs the specified runnable
     *
     * The argument depend on the implementation and thus should be
     * retrieved using func_get_args.
     *
     * @param mixed implementation specific
     * @return mixed implementation specific
     */
    function run();
}
