<?php
/**
 * Created by PhpStorm.
 * User: eapbachman
 * Date: 13/07/14
 * Time: 00:48
 */

namespace Tesla\EsyncBundle\Event;


class EsyncEvents {

    const CREATE = 'cud_event.create';
    const DELETE = 'cud_event.delete';
    const UPDATE = 'cud_event.update';

    const START_UOW = 'cud_event.start_uow';
    const END_UOW = 'cud_event.end_uow';
} 