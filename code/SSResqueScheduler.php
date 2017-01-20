<?php
/**
 * This controller starts a long lived process that will execute resque jobs
 *
 * Typically you will start it by running this in a cli environment
 *
 *     ./framework/sake dev/resque/run verbose=1 queue=* flush=1
 *
 * list of GET params:
 *
 *  verbose: 1 | 0 -  Should we log all messages to the log
 *  queue: "queuename" - A comma separated list of queues to work on
 *  backend: "localhost:6379" - the address and port number of the redis server
 *  count: int - the number of child workers to spin up
 */
class SSResqueScheduler extends Controller {

    /**
     *
     * @var array
     */
    public static $allowed_actions = array(
        'index',
    );

    /**
     *
     * @var mixed $backend Host/port combination separated by a colon, or
     *                     a nested array of servers with host/port pairs
     */
    protected $backend = null;

    /**
     *
     * @var Psr\Log\AbstractLogger
     */
    protected $logger = null;

    /**
     * How often to check for new jobs on the queue in seconds
     *
     * @var int
     */
    protected $interval = 1;


    /**
     * Check that all needed and option params have been set
     *
     *
     */
    public function init() {
        // Ensure the composer autoloader is loaded so dependencies are loaded correctly
        //require_once BASE_PATH.'/vendor/autoload.php';

        parent::init();

        if(php_sapi_name() !== 'cli') {
            echo 'The resque runner must be started in a CLI environment.';
            exit(1);
        }

        if($this->request->getVar('backend')) {
            Resque::setBackend($this->request->getVar('backend'));
        }

        $this->logger = new SSResqueLogger((bool) $this->request->getVar('verbose'));
    }

    /**
     * This is where the action starts
     *
     * @param SS_HTTPRequest $request
     */
    public function index(SS_HTTPRequest $request) {
        $this->startWorker();
    }

    /**
     * Start a single worker
     */
    protected function startWorker() {
        $worker = new SSResqueScheduler_Worker();
        $worker->setLogger($this->logger);
        $PIDFILE = getenv('PIDFILE');

        if ($PIDFILE) {
            file_put_contents($PIDFILE, getmypid()) or die('Could not write PID information to ' . $PIDFILE);
        }
    }
}

class SSResqueScheduler_Worker extends ResqueScheduler_Worker
{
    /**
     * @var Psr\Log\AbstractLogger
     */
    protected $logger;

    /**
     * @param Psr\Log\AbstractLogger $logger
     * @return $this
     */
    public function setLogger($logger)
    {
        $this->logger = $logger;
        return $this;
    }

    public function log($message)
    {
        $this->logger->log(Psr\Log\LogLevel::NOTICE, $message);
    }
}
