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

        $content = $this->cacheHelper->getContent($request->getRequestTarget());

        if ($content != null) {
            $dataContentCache = $content;
        } else {
            $dataContentCache = $callable();

            if ( !is_string($dataContentCache) || $dataContentCache === '') {
                throw new InvalidArgumentException('Callable must be return a string');
            }

            $this->writeCache($request, $dataContentCache);
        }

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

        if ($this->requestTarget) {
            $content = $this->cacheHelper->getContent($this->requestTarget);
        } else {
            $content = $this->cacheHelper->getContent($request->getRequestTarget());
        }

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

        $this->saltAddToPath = null;
        $this->requestTarget = null;

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
     * Set add new optional path name into the current path
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
        $requestTarget = $request->getRequestTarget();

        if ($this->requestTarget) {
            $requestTarget = $this->requestTarget;
        }

        if ($this->saltAddToPath) {
            $requestTarget = $requestTarget .'/' .$this->saltAddToPath;
        }

        $this->cacheHelper->setContent($requestTarget, $content);
        $this->cacheHelper->setExpires($requestTarget, $this->times);
    }
}