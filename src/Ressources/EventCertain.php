<?php

namespace Wabel\CertainAPI\Ressources;

use Wabel\CertainAPI\Interfaces\CertainRessourceInterface;
use Wabel\CertainAPI\CertainRessourceAbstract;

/**
 * EventCertain about the Event entity
 *
 * @author rbergina
 */
class EventCertain extends CertainRessourceAbstract implements CertainRessourceInterface
{

    public function getRessourceName()
    {
        return 'Event';
    }

    public function getMandatoryFields()
    {
        return array("eventCode", "accountCode", "eventName",
            "eventStatus", "notes", "startDate",
            "endDate", "timezone");
    }
}