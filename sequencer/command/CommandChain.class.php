<?php
/**
 * CommandChain class definition
 *
 * @author Yoann Mikami <yoann.mikami@gmail.com>
 */

/**
 * CommandChain defines a sequences of runnable processes that
 * can be ran as a batch by invoking CommandChain::run().
 * CommandChain::run take the expected input parameters for the first
 * runnable in the chain; then, each subsequent runnable in the chain
 * takes as an input the output of the previous runnable.
 * E.g. If CommandChain Z contains 4 runnables A, B, C, D :
 * <code>
 * $Z->run(array('foo'=>'foo', 'bar'=>'bar'));
 * </code>
 * Will roughly produce the following sequence (provided no runnable fail) :
 * <code>
 * $D->run($C->run($B->run($A->run(array('foo'=>'foo', 'bar'=>'bar')))));
 * </code>
 * Should the chain be stopped, such as when an error occurred within
 * the runnable, the runnable should raise a RunnableException.
 *
 * In addition, a context can be specified to the run method :
 * Contrary to the arguments, which change for every runnable, context
 * is an array of parameters that are immutable throughout the sequence,
 * thus allowing each runnable to access the same properties altogether.
 */
final class CommandChain
{
    /**
     * @var array list of runnable commands
     */
    private $_runnables = array();


    /**
     * Queues a new runnable in the chain
     *
     * @todo handle the $after parameter and $name parameters
     * @todo add new features
     *
     * @param @type IRunnable $cmd the runnable to queue
     * @param @type string $name the runnable name
     * @param @type string $after name of the runnable after which to queue $cmd
     * @return $this for chaining
     */
    public function queue(IRunnable $cmd, $name, $after=null)
    {
        $this->_doQueue($cmd, $name, $after);
        return $this;
    }

    /**
     * Runs the registered runnables
     *
     * If a RunnableException is raised during the execution,
     * the command stops at this point and returns null.
     *
     * @param array $args initial parameters for first runnable in queue
     * @param array $context that gets passed from runnable to the next
     * @return mixed|null Depends on the last runnable executed, or null if error
     * @throw @type RunnableException when something wrong happens
     */
    public function run($args, &$context=array())
    {
        if (!is_array($args)) {
            $args = array($args);
        }
        return $this->_doRun($args, $context);
    }


    private function _doQueue(IRunnable $cmd, $name, $after=null)
    {
        $this->_runnables[$name]  = $cmd;
    }

    private function _doRun(array $args, array &$context)
    {
        try {
            foreach ($this->_runnables as $runnable) {
                // Sets the context before executing
                $runnable->setContext($context);
                // Wrap into an array when chaining
                $args = array(call_user_func_array(array($runnable, 'run'), $args));
                // If the runnable changed the context, retrieve it
                $context = $runnable->getContext();
            }
        }
        catch (RunnableException $re)
        {
            sfContext::getInstance()->getLogger()->err($re->__toString());
            throw $re;
        }
        return array_shift($args);
    }
}
