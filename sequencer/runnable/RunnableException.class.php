<?php
/**
 * RunnableException exception definition
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * RunnableException defines an exception that IRunnable implementations
 * should raise should an error occur when a CommandChain is running.
 * It takes the IRunnable that raised it as a parameter.
 */
final class RunnableException extends Exception
{
    private $_runnable = null; /**< @type IRunnable the runnable */

    public function __construct($mesg, IRunnable $runnable)
    {
        $this->_runnable = $runnable;
        parent::__construct($mesg);
    }

    public function __toString()
    {
        return '['.get_class($this->_runnable).'] '.parent::__toString();
    }
}
