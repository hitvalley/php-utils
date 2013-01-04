<?php
/**
 * Base implementation class definition for IRunnable
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * Runnable class provides a base abstract class for IRunnable.
 * It provides with a very basic argument prerequisite check,
 * such as the expected number of args, and a type check of each of these args.
 * For the rest, refer to @see CommandChain or @see IRunnable .
 */
abstract class Runnable implements IRunnable
{
    private $_ensureArgCount = 0;       /**< @type integer expected # of args for run */
    private $_ensureArgTypes = array(); /**< @type array list of expected args for run */

    protected $_context = array();      /**< @type array context values passed during run */


    /**
     * Initializes the runnable through _init()
     *
     * Subclasses should override _init for initialization.
     */
    public function __construct()
    {
        $this->_init();
    }

    /**
     * Sets the context
     *
     * @param array $context the context to set
     * @return $this for chaining
     */
    final public function setContext(array $context)
    {
        $this->_context = $context;
        return $this;
    }

    /**
     * Returns the saved context
     *
     * @return @type array the context
     */
    final public function getContext()
    {
        return $this->_context;
    }

    /**
     * Runs the runnable
     *
     * Parameters are dependent on each implementation, and are thus retrieved
     * using func_get_args.
     * Prerequisites are checked before running the actual process.
     *
     * @param mixed subclass specific
     * @return mixed subclasss specific
     */
    final public function run()
    {
        if ($this->_ensureArgCount !== func_num_args()) {
           throw new RunnableException(
                    sprintf('Invalid number of arguments : expected %d, got %d',
                            $this->_ensureArgCount, func_num_args()), $this);
        }
        $args = func_get_args();
        foreach ($this->_ensureArgTypes as $idx => $fType)
        {
            if (!isset($args[$idx]) || !$fType($args[$idx])) {
               throw new RunnableException(
                        sprintf('Invalid argument type (%d) : expected %s, got %s',
                                $idx, $type, gettype($args[$idx])), $this);
            }
        }
        return $this->_doRun($args);
    }


    protected function _init() { }


    protected function _setEnsureArgCount($cnt)
    {
        $this->_ensureArgCount = $cnt;
    }

    protected function _addEnsureArgType($idx, $fType)
    {
        $this->_ensureArgTypes[$idx] = $fType;
    }


    /**
     * Main method called by run. Where the actual stuff happens.
     *
     * @param array arguments passed to run
     * @return mixed subclass specific
     */
    abstract protected function _doRun(array $args);
}
