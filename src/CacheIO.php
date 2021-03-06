<?php

namespace LoveCoding\ContentCache;

/**
* 
*/
class CacheIO
{
    private static $instance;

    /**
     * @return mixed
     */
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Read content of file cache in system
     *
     * @param $file
     * @return bool|null|string
     */
    public function readContentFileCache(string $file)
    {
        $content = null;
        $checkFile = false;

        if ( file_exists($file) ) {
            $readFile = @fopen($file, "r");

            while( !feof($readFile) ) {
                $dataLine = @fgets($readFile);

                if ($dataLine != '') {
                    $checkFile = true;
                    break;
                }
            }

            @fclose($readFile);
        }

        if ( $checkFile == true ) {
            $readFile = @fopen($file, "r");

            $data = @fread($readFile, filesize($file));

            @fclose($readFile);

            $content = $data;
        }

        return $content;
    }

    /**
     * Write content of cache to system
     *
     * @param $file
     * @param $content
     */
    public function writeContentFileCache(string $file, string $content)
    {
        $writeFile = @fopen($file, "w+");

        @fwrite($writeFile, $content);
        @fclose($writeFile);
    }

    /**
     * Delete file contain content of cache
     *
     * @param string $file
     */
    public function deleteFileCache($file)
    {
        if (file_exists($file)) {
            @unlink($file);
        }
    }

    /**
     * Read time expires of cache in system
     *
     * @param $file
     * @return bool|null|string
     */
    public function readExpires(string $file)
    {
        $data = null;
        $checkFile = false;

        if ( file_exists($file) ) {
            $readFile = @fopen($file, "r+");

            while( !feof($readFile) ) {
                $dataLine = @fgets($readFile);
                if ( $dataLine != '' ) {
                    $checkFile = true;
                }
            }
            @fclose($readFile);
        }

        if ( $checkFile ) {
            $readFile = @fopen($file, "r+");

            $data = @fread($readFile, @filesize($file));

            @fclose($readFile);
        }

        return $data;
    }

    /**
     * Write time expires of cache to system
     *
     * @param $file
     * @param $format
     * @param int $hours
     * @param int $minutes
     * @param int $second
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function writeExpires(string $file, string $format, int $hours = 0, int $minutes = 0,
            int $second = 0, int $day = 0, int $month = 0, int $year = 0)
    {
        $datetime = getdate();

        $alarm = mktime (($datetime['hours'] + $hours), ($datetime['minutes'] + $minutes), ($datetime['seconds'] + $second),
            ($datetime['mon'] + $month), ($datetime['mday'] + $day), ($datetime['year'] + $year));

        $alarm = date($format, $alarm);

        $writeFile = @fopen($file, "w+");

        @fwrite($writeFile, $alarm);
        @fclose($writeFile);
    }

    /**
     * Delete file contain time expires
     *
     * @param $file
     */
    public function deleteFileExpires(string $file)
    {
        if (file_exists($file)) {
            @unlink($file);
        }
    }
}