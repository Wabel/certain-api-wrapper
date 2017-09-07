<?php

namespace Wabel\CertainAPI\Interfaces;


interface CertainListener
{
    /**
     * @param string $eventCode
     * @param array $elements
     * @return void
     */
    public function run($eventCode, array $elements);

}