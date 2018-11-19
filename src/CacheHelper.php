<?php
namespace LoveCoding\ContentCache;

use RuntimeException;

class CacheHelper {
    private static $instance;
    private $datetimeFormat = 'Y-m-d H:i:s';
    private $timezone = 'Asia/Ho_Chi_Minh';
    private $isExpires = true;
    private $rootDirCache;

    public function __construct(string $rootDirCache)
    {
        // check write permission
        $this->checkWritePermissionDirectory($rootDirCache);
        $this->rootDirCache = $rootDirCache;
    }

    /**
     * @param $rootDirCache
     * @return CacheService
     */
    public static function getInstance(string $rootDirCache)
    {
        if (self::$instance == null) {
            self::$instance = new self($rootDirCache);
        }
        return self::$instance;
    }

    public function isExpires(bool $isExpires)
    {
        $this->isExpires = $isExpires;
    }

    /**
     * Get content in cache, if now > than time expires => delete old file cache and return null
     * If cache is exist and now < time expires => get content => return content by array
     *
     * @param $requestTarget
     * @return array|mixed|null
     */
    public function getContent(string $requestTarget)
    {
        $cacheIo = CacheIO::getInstance();

        // Create path base on request of end-user, this path is dir content cache file
        $requestTargetContent = $this->pathGenerator($requestTarget) .'_content';

        if ($this->isExpires) {
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
        }

        // Read file content
        $content = $cacheIo->readContentFileCache($requestTargetContent);

        // Remove folder content cache if get content fail (may be, when get data occur error so, we will remove unnecessary folder)
        if ($content == null || $content == '') {
            $cacheIo->deleteFileCache($requestTargetContent);
            if ($this->isExpires) {
                $cacheIo->deleteFileExpires($requestTargetExpires);
            }
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
    public function setContent(string $requestTarget, string $content)
    {
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
    public function setExpires(string $requestTarget, int $hours = 0, int $minutes = 0, int $second = 0,
        int $day = 0, int $month = 0, int $year = 0)
    {
        if ($this->isExpires) {
            // create path save file contain time expires
            $requestTarget = $this->pathGenerator($requestTarget) .'_schedule';

            $cacheIo = CacheIO::getInstance();
            $cacheIo->writeExpires($requestTarget, $this->datetimeFormat, $hours, $minutes, $second, $day, $month, $year);
        }
    }

    /**
     * @param $requestTarget
     * @return string $path the path of file to cache
     */
    public function pathGenerator(string $requestTarget)
    {
        $unsafeDirectoryPath = urldecode(
            parse_url($requestTarget, PHP_URL_PATH)
        );

        $safeDirectoryPath = md5($unsafeDirectoryPath);

        $cacheRootPath = $this->rootDirCache .'/' .$safeDirectoryPath;

        // create folder with path if it's not exist.
        Utils::createMultipleFolder($cacheRootPath);

        // create path of file
        $path = $cacheRootPath .'/' .md5($requestTarget);

        return $path;
    }

    public function checkWritePermissionDirectory(string $dir)
    {
        if (!is_writable(dirname($dir))) {
            throw new RuntimeException('Cache\'s root directory must be writable');
        }
    }

    /**
     * Set datetime format
     *
     * @param $format
     */
    public function setDatetimeFormat(string $format)
    {
        if ($format !== '') {
            $this->datetimeFormat = $format;
        }
    }

    /**
     * Set default timezone
     * @param Datestring $timezone
     */
    public function setTimezone(string $timezone)
    {
        if ($timezone !== '') {
            $this->timezone = $timezone; 
        }

        date_default_timezone_set($this->timezone);
    }

    /**
     * Convert json to array object
     * @param  String $json Json string
     * @return Array  array
     */
    public function json_2_array(string $json) : array
    {
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
    public function array_2_json(array $array) : string
    {
        if ($array == null) {
            return '{}';
        }
        return json_encode($array);
    }
}