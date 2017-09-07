<?php
namespace Wabel\CertainAPI\Ressources;

use Wabel\CertainAPI\Interfaces\CertainRessourceInterface;
use Wabel\CertainAPI\CertainRessourceAbstract;

/**
 * AppointementsCertain about the Appointements entity
 *
 * @author rbergina
 */
class AppointmentsCertain extends CertainRessourceAbstract implements CertainRessourceInterface
{
    public function getRessourceName(){
        return 'Appointments';
    }

    public function getMandatoryFields()
    {
        return array();
    }
}