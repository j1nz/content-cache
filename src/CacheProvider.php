<?php

namespace LoveCoding\ContentCache;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
/**
* 
*/
class CacheProvider
{
    private $cacheService;
    // Default save contents on dish with 4 hours
    private $times = 4;

    public function __construct($cacheRootDir) {
        $this->cacheService = CacheService::getInstance($cacheRootDir);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param callable $callable
     * @return mixed
     */
    public function cache(Request $request, Response $response, callable $callable) {
        $dataContent = null;

        if ($callable && is_callable($callable)) {
            $content = $this->cacheService->getContent($request->getRequestTarget());

            if ($content != null) {
                $dataContent = $content;
            } else {
                $dataContent = $callable();

                if ($dataContent != null) {
                    $this->writeCache($request, $dataContent);
                }
            }
        } else {
            throw new \InvalidArgumentException('Must be a callback function');
        }

        return $dataContent;
    }

    public function writeCache(Request $request, array $content) {
        $this->cacheService->setContent($request->getRequestTarget(), $content);
        $this->cacheService->setExpires($request->getRequestTarget(), $this->times);
    }

    public function withExpires($hours) {
        $this->times = $hours;
        return $this;
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
}