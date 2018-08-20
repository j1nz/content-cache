<?php
namespace LoveCoding\CacheHelper;

use RuntimeException;

class CacheHelper {
    private static $instance;
    private $datetimeFormat = 'Y-m-d H:i:s';
    private $timezone = 'Asia/Ho_Chi_Minh';
    private $rootDirCache;

    public function __construct($rootDirCache) {
        // check write permission
        $this->checkWritePermissionDirectory($rootDirCache);
        $this->rootDirCache = $rootDirCache;
    }

    /**
     * @param $rootDirCache
     * @return CacheService
     */
    public static function getInstance($rootDirCache) {
        if (self::$instance == null) {
            self::$instance = new self($rootDirCache);
        }
        return self::$instance;
    }

    /**
     * Get content in cache, if now > than time expires => delete old file cache and return null
     * If cache is exist and now < time expires => get content => return content by array
     *
     * @param $requestTarget
     * @return array|mixed|null
     */
    public function getContent($requestTarget) {
        $cacheIo = CacheIO::getInstance();

        // Create path base on request of end-user, this path is dir content cache file
        $requestTargetContent = $this->pathGenerator($requestTarget) .'_content';
        $requestTargetExpires = $this->pathGenerator($requestTarget) .'_schedule';

        // Get current time (now) and time config cache
        $timeNow = date($this->datetimeFormat);
        $timeCraw = $cacheIo->readExpires($requestTargetExpires);

        // Convert datetime to timestamp
        $timeCrawStamp = strtotime($timeCraw);
        $timeNowStamp = strtotime($timeNow);

        // Compared time now and time expires, now > time expires => delete old file cache
        if ($timeNowStamp > $timeCrawStamp) {
            $cacheIo->deleteFileCache($requestTargetContent);
            $cacheIo->deleteFileExpires($requestTargetExpires);
            return null;
        }

        // Read file content
        $content = $cacheIo->readContentFileCache($requestTargetContent);

        // Remove folder content cache if get content fail (may be, when get data occur error so, we will remove unnecessary folder)
        if ($content == null || $content == '') {
            $cacheIo->deleteFileCache($requestTargetContent);
            $cacheIo->deleteFileExpires($requestTargetExpires);
            return null;
        }

        return $content;
    }

    /**
     * Write content to file cache, with format json will be save
     *
     * @param $requestTarget
     * @param array $data
     */
    public function setContent($requestTarget, $content) {
        //$requestTarget = strtolower($requestTarget);

        // create path save file cache
        $requestTarget = $this->pathGenerator($requestTarget) .'_content';

        $cacheIo = CacheIO::getInstance();

        $cacheIo->writeContentFileCache($requestTarget, $content);
    }

    /**
     * Write time expires of cache
     *
     * @param string $requestTarget
     * @param int $hours
     * @param int $minutes
     * @param int $second
     * @param int $day
     * @param int $month
     * @param int $year
     */
    public function setExpires($requestTarget, $hours = 0, $minutes = 0, $second = 0, $day = 0, $month = 0, $year = 0) {
        // create path save file contain time expires
        $requestTarget = $this->pathGenerator($requestTarget) .'_schedule';

        $cacheIo = CacheIO::getInstance();
        $cacheIo->writeExpires($requestTarget, $this->datetimeFormat, $hours, $minutes, $second, $day, $month, $year);
    }

    /**
     * @param $requestTarget
     * @return string $path the path of file to cache
     */
    public function pathGenerator($requestTarget) {
        $autoFolder = urldecode(
            parse_url($requestTarget, PHP_URL_PATH)
        );

        $cacheRootPath = $this->rootDirCache .$autoFolder;

        // create folder with path if it's not exist.
        Utils::createMultipleFolder($cacheRootPath);

        // create path of file
        $path = $cacheRootPath .'/' .strtolower(base64_encode($requestTarget));

        return $path;
    }

    public function checkWritePermissionDirectory($dir) {
        if (!is_writable(dirname($dir))) {
            throw new RuntimeException('Cache\'s root directory must be writable');
        }
    }

    /**
     * Set datetime format
     *
     * @param $format
     */
    public function setDatetimeFormat($format) {
        if ($format !== null) {
            $this->datetimeFormat = $format;
        }
    }

    /**
     * Set default timezone
     * @param Datestring $timezone
     */
    public function setTimezone($timezone = null) {
        if ($timezone !== null) {
            $this->timezone = $timezone; 
        }

        date_default_timezone_set($this->timezone);
    }

    /**
     * Convert json to array object
     * @param  String $json Json string
     * @return Array  array
     */
    public function json_2_array($json) {
        if ($json == '') {
            return [];
        }
        return json_decode($json, true);
    }

    /**
     * Convert array to json string
     * @param  array  $array Array object
     * @return String        Json string
     */
    public function array_2_json(array $array) {
        if ($array == null) {
            return '{}';
        }
        return json_encode($array);
    }
}