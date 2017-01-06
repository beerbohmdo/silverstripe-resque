<?php

abstract class SSResqueJob extends Object implements Resque_JobInterface
{
    /**
     * @var array
     */
    public $args;

    /**
     * @var string
     */
    public $queue;

    /**
     *
     * @global array $databaseConfig
     */
    public function setUp()
    {
        global $databaseConfig;
        DB::connect($databaseConfig);
        chdir(BASE_PATH);
    }
}