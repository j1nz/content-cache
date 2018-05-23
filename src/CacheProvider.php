<?php

namespace LoveCoding\ContentCache;

use \Psr\Http\Message\ServerRequestInterface as Request;
/**
* 
*/
class CacheProvider
{
    private $cacheService;
    // Default save contents on dish with 4 hours
    private $times = 4;
    private $timezone = '';

    public function __construct($cacheRootDir) {
        $this->cacheService = CacheService::getInstance($cacheRootDir);
    }

    public function cache(Request $request, callable $callable) {
        $dataContentCache = null;

        $content = $this->cacheService->getContent($request->getRequestTarget());

        if ($content != null) {
            $dataContentCache = $content;
        } else {
            $dataContentCache = $callable();

            if ($dataContentCache != null) {
                $this->writeCache($request, $dataContentCache);
            }
        }

        return $dataContentCache;
    }

    public function cacheArray(Request $request, callable $callable) {
        $dataContentCache = null;

        $content = $this->cacheService->getContent($request->getRequestTarget());

        if ($content != null) {
            $dataContentCache = $this->json_2_array($content);
        } else {
            $array = $callable();

            if ( !$array && !is_array( $array ) ) {
                throw new \InvalidArgumentException('Callback must be return an array');
            }

            $dataContentCache = $array;

            if ($dataContentCache != null && $dataContentCache != '{}') {
                $this->writeCache($request, $this->array_2_json($array));
            }
        }

        return $dataContentCache;
    }

    public function withExpires($hours) {
        $this->times = $hours;
        return $this;
    }

    public function setTimezone($timezone) {
        date_default_timezone_set($timezone);
    }

    public function json_2_array($json) {
        if ($json == '') {
            return [];
        }
        return json_decode($json, true);
    }

    public function array_2_json(array $array) {
        if ($array == null) {
            return '{}';
        }
        return json_encode($array);
    }

    private function writeCache(Request $request, $content) {
        $this->cacheService->setContent($request->getRequestTarget(), $content);
        $this->cacheService->setExpires($request->getRequestTarget(), $this->times);
    }
}