<?php

namespace Wabel\CertainAPI\Helpers;


class FileChangesHelper
{
    /**
     * Create the directory if it doesn't exist. Right:0775
     * @param $dirPath
     * @return bool
     */
    public static function createDirectory($dirPath){
        if(!file_exists($dirPath)){
            return mkdir($dirPath, 0775, true, null);
        }
        return false;
    }

    /**
     * Write the file. The content is already in json.
     * @param string $path
     * @param string $msg
     * @return void
     */
    public static function writeFile($path,$msg){
        $f = fopen($path, "w+");
        fwrite($f, $msg);
        fclose($f);
    }

    /**
     * Remove the file
     */
    public static function removeFile(string $path): void{
        unlink($path);
    }

    /**
     * Save appointments with the timestamp in filename.
     * @param string $filePath
     * @param string $contents
     * @return void
     */
    public static function saveAppointmentsFileByHistory($filePath,$contents){
        $time = time();
        $file = pathinfo($filePath);
        $fileNamebase = $file['filename'];
        $filePath = $file['dirname'].'/'.$fileNamebase.'_'.$time.'.json';
        if(!file_exists($filePath)){
            self::writeFile($filePath,'');
        }
        self::writeFile($filePath,$contents);
    }

    /**
     * Get the content from a file.
     * @param $path
     * @return string
     */
    public static function getContentFile($path){
        if(!file_exists($path)){
            self::writeFile($path,'');
        }
        $handle = fopen($path, "rb");
        if (FALSE === $handle) {
            $handle = fopen($path, "rb");
        }
        $contents = '';
        while (!feof($handle)) {
            $contents .= fread($handle, 8192);
        }
        fclose($handle);
        return $contents;
    }

    /**
     * Get the content from a json file by decoding.
     * @param string $path
     * @return mixed
     */
    public static function getJsonContentFromFile($path){
        $contents = self::getContentFile($path);
        return json_decode($contents, true);
    }

    /**
     * Get an  file list of appointments by eventCode
     * @param string $eventCode
     * @param string $pathDir
     * @return array
     */
    public static function getFilesListHistoryAppointmentsByEventCode($eventCode,$pathDir){
        $fileList = array();
        $files = glob($pathDir.'/appointments_'.$eventCode.'_*.json');
        foreach ($files as $file) {
            $fileList[filemtime($file)] = $file;
        }
        ksort($fileList);
        $fileList = array_reverse($fileList, TRUE);
        return $fileList;
    }

    /**
     * Get the last list of appointments.
     * @param string $eventCode
     * @param string $pathDir
     * @return string
     */
    public static function getTheLastAppointmentsSaved($eventCode,$pathDir){
        $fileList = self::getFilesListHistoryAppointmentsByEventCode($eventCode,$pathDir);
        return array_shift($fileList);
    }

    public static function checkDirectory(string $pathDir): string
    {
        if (!is_dir($pathDir)) {
            return 'no_directory';
        }
        if (!is_readable($pathDir)) {
            return 'not_readable';
        }
        if (!is_writable($pathDir)) {
            return 'not_writable';
        }
        return 'ok';
    }

    public static function commandIsLocked(string $pathDir): bool
    {
        return file_exists($pathDir. '/command.lock');
    }

    public static function lockCommand(string $pathDir): void
    {
        if (!self::commandIsLocked($pathDir)) {
            self::writeFile($pathDir. '/command.lock','1');
        }
    }

    public static function unlockCommand(string $pathDir): void
    {
        if (self::commandIsLocked($pathDir)) {
            self::removeFile($pathDir. '/command.lock');
        }
    }
}