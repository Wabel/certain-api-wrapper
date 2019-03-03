<?php

namespace Wabel\CertainAPI\Interfaces;


interface CertainListener
{
    /**
     * @param string $eventCode
     * @param array $elements
     * @return void
     */
    public function run(string $eventCode, array $elements, array $options = []);

}