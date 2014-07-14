<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13/07/14
 * Time: 02:24
 */

namespace Tesla\EsyncBundle\Logger;


use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

class LoggerFactory implements LoggerAwareInterface {

    private $logger;
    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
       $this->logger = $logger;
    }

    function get() {
        return $this->logger;
    }
} 