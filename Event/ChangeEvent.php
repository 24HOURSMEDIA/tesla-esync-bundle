<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13/07/14
 * Time: 00:49
 */

namespace Tesla\EsyncBundle\Event;


use Symfony\Component\EventDispatcher\Event;
use Tesla\Esync\Message\CudMessage;

class ChangeEvent extends Event {


    protected $message;

    function __construct(CudMessage $message) {
        $this->message = $message;
    }

    function getMessage($message) {
        return $this->message;
    }
} 