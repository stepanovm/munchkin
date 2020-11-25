<?php


namespace Lynxx;


use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class Logger extends AbstractLogger implements LoggerInterface
{
    private string $logPath;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * Logger constructor.
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->logPath = __DIR__ . '/../log/';
        $this->request = $request;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, string $message, array $context = [])
    {
        $serverParams = $this->request->getServerParams();
        $msg = '_________________' . PHP_EOL . date("Y-m-d H:m:s");
        $msg .= PHP_EOL . 'Request url: ' . $this->request->getUri();
        $msg .= PHP_EOL . 'Message: ' . $message;
        $msg .= PHP_EOL . '***** Request info: ';
        $msg .= PHP_EOL . 'user ip: ' . $serverParams['REMOTE_ADDR'];
        if(array_key_exists('HTTP_REFERER', $serverParams)){
            $msg .= PHP_EOL . 'referer: ' . $serverParams['HTTP_REFERER'];
        }
        $msg .= PHP_EOL . 'useragent: ' . $this->request->getHeader('user-agent')[0];

        if (isset($context['throwable']) && $context['throwable'] instanceof \Throwable) {
            $ex = $context['throwable'];
            $msg .= PHP_EOL . '***** Trace:';
            $msg .= PHP_EOL . 'currfile:' . $ex->getFile();
            $msg .= '; line: ' . $ex->getLine();
            foreach ($ex->getTrace() as $trace) {
                $msg .= PHP_EOL . 'file: ' . $trace['file'];
                $msg .= '; line: ' . $trace['line'];
            }
        }

        if(!is_dir($this->logPath)){
            mkdir($this->logPath);
        }
        file_put_contents($this->logPath . $level, $msg . PHP_EOL, FILE_APPEND);
    }
}