<?php

namespace LoveCoding\ContentCache;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Cache content provider
 * Author: ThangLe
 */
class CacheProvider {
    private $cacheHelper;
    // Default save contents on dish with 4 hours
    private $times = 4;
    private $saltAddToPath;
    private $requestTarget;

    public function __construct($cacheRootDir) {
        $this->cacheHelper = CacheHelper::getInstance($cacheRootDir);
    }

    /**
     * Cache any type to cache dir in server
     * @param  Request  $request
     * @param  callable $callable
     * @return mixed
     */
    public function cache(Request $request, callable $callable) {
        $dataContentCache = null;

        $content = $this->readCache($request);

        if ($content != null) {
            $dataContentCache = $content;
        } else {
            $dataContentCache = $callable();

            if ( !is_string($dataContentCache) || $dataContentCache === '') {
                throw new InvalidArgumentException('Callable must be return a string');
            }

            $this->writeCache($request, $dataContentCache);
        }

        $this->rollbackPropertiesDefault();

        return $dataContentCache;
    }

    /**
     * Cache only array to cache dir in server
     * @param  Request  $request 
     * @param  callable $callable
     * @return Array
     */
    public function cacheArray(Request $request, callable $callable) {
        $dataArrayCache = null;

        $content = $this->readCache($request);

        if ($content != null) {
            $dataArrayCache = $this->cacheHelper->json_2_array($content);
        } else {
            $dataArrayCache = $callable();

            if ( !is_array($dataArrayCache) ) {
                throw new InvalidArgumentException('Callable must be return an array');
            }

            if ($dataArrayCache != '{}') {
                $this->writeCache($request, $this->cacheHelper->array_2_json($dataArrayCache));
            }
        }

        $this->rollbackPropertiesDefault();

        return $dataArrayCache;
    }

    /**
     * Set expires hours for content cache 
     * @param  Integer $hours
     * @return static        
     */
    public function withExpires($hours) {
        $this->times = $hours;
        return $this;
    }

    /**
     * Add new optional path name into the current path
     * @param  String $salt
     * @return static       
     */
    public function salt($salt) {
        $this->saltAddToPath = $salt;
        return $this;
    }

    /**
     * Custome request target when don't want use tradiotional way
     * @param  String $target Replace $request->getRequestTarget() is default
     * @return static
     */
    public function customRequestTarget($target) {
        $this->requestTarget = $target;
        return $this;
    }

    /**
     * Write cache using cache helper class
     * Write content and expires times
     * @param  Request $request Get current path
     * @param  String  $content Content needed cache
     */
    private function writeCache(Request $request, $content) {
        $requestTarget = $this->createTargetPath($request);

        $this->cacheHelper->setContent($requestTarget, $content);
        $this->cacheHelper->setExpires($requestTarget, $this->times);
    }

    /**
     * Read cache from disk
     * @param  Request $request Client request
     * @return mixed           Content have been cached
     */
    private function readCache(Request $request) {
        $requestTarget = $this->createTargetPath($request);
        return $this->cacheHelper->getContent($requestTarget);
    }

    /**
     * generetor path to disk content cache
     * @param  Request $request Client request
     * @return String           Target path to disk content cache
     */
    private function createTargetPath(Request $request) {
        $requestTarget = $request->getRequestTarget();

        if ($this->requestTarget) {
            $requestTarget = $this->requestTarget;
        }

        if ($this->saltAddToPath) {
            $requestTarget = $requestTarget .'/' .$this->saltAddToPath;
        }

        return $requestTarget;
    }

    /**
     * Set default value for properties
     */
    private function rollbackPropertiesDefault() {
        $this->saltAddToPath = null;
        $this->requestTarget = null;
        $this->times = 4;
    }
}