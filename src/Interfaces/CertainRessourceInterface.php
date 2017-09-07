<?php

namespace Wabel\CertainAPI\Interfaces;

/**
 * CertainRessourceInterface
 *
 * @author rbergina
 */
interface CertainRessourceInterface
{
    public function getRessourceName();

    public function getMandatoryFields();
}