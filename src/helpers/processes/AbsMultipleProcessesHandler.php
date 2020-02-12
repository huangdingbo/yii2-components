<?php


namespace dsj\components\helpers\processes;



abstract class AbsMultipleProcessesHandler
{
    protected $process;

    protected $params;

    public function __construct($process,array $params = [])
    {
        $this->process = $process;

        $this->params = $params;
    }

    public abstract function execute();

    public abstract static function className();
}