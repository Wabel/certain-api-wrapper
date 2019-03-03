<?php

namespace Wabel\CertainAPI\Listeners;


use Wabel\CertainAPI\Helpers\FileChangesHelper;
use Wabel\CertainAPI\Interfaces\CertainListener;

class ChangingsToFileListeners implements CertainListener
{

    /**
     * @var string
     */
    private $dirPathChangings;

    /**
     * ChangingsToFileListeners constructor.
     * @param string $dirPathChangings
     */
    public function __construct($dirPathChangings)
    {

        $this->dirPathChangings = $dirPathChangings;
    }

    /**
     * @param string $eventCode
     * @param array $elements
     * @return void
     */
    public function run(string $eventCode,array $elements, array $options = [])
    {
        if(isset($elements['updated'])){
            foreach ($elements['updated'] as $element){
                if(!is_array($element)){
                    $element = [$element];
                }
                $this->updateUpdateListFile($eventCode,$element);
            }
        }
        if(isset($elements['deleted'])){
            foreach ($elements['deleted'] as $element){
                if(!is_array($element)){
                    $element = [$element];
                }
                $this->updateDeleteListFile($eventCode,$element);
            }
        }
    }


    /**
     * @param sring $filePath
     * @param array $element
     * @return void
     */
    private function updateFile($filePath,array $element){
        $content = FileChangesHelper::getJsonContentFromFile($filePath);
        $content[] = $element;
        FileChangesHelper::writeFile($filePath,json_encode($content));
    }

    /**
     * @param string $eventCode
     * @param array $element
     * @return void
     */
    private function updateUpdateListFile($eventCode,array $element){
        FileChangesHelper::createDirectory($this->dirPathChangings);
        $fileName = 'update_'.$eventCode.'.json';
        $this->updateFile($this->dirPathChangings.'/'.$fileName,$element);
    }

    /**
     * @param string $eventCode
     * @param array $element
     * @return void
     */
    private function updateDeleteListFile($eventCode,array $element){
        FileChangesHelper::createDirectory($this->dirPathChangings);
        $fileName = 'delete_'.$eventCode.'.json';
        $this->updateFile($this->dirPathChangings.'/'.$fileName,$element);
    }
}