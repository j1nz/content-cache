<?php

namespace lovecoding\ContentCache;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Container\ContainerInterface as Container;
/**
* 
*/
class CacheProvider
{
    
    private $cacheBo;
    // Default save contents on dish with 4 hours
    private $times = 4;

    public function __construct($cacheRootDir) {
        $this->cacheBo = CacheBO::getInstance($cacheRootDir);
    }

    /**
     * @param Request $request
     * @param Response $response
     * @param $container
     * @param callable $callable
     * @return mixed
     */
    public function cache(Request $request, Response $response, Container $container, callable $callable) {
        $dataContent = null;

        if ($callable && is_callable($callable)) {
            $content = $this->cacheBo->getContent($request->getRequestTarget());

            if ($content != null) {
                $dataContent = $content;
            } else {
                $dataContent = $callable();

                if ($dataContent != null) {
                    $this->writeCache($request, $dataContent);
                }
            }

            //$dataResponse = $container->get('responseService')->responseResult($dataArray);
        } else {
            throw new \InvalidArgumentException('Must be a callback function');
        }

        return $dataContent;
        //return $response->withJson($dataResponse);;
    }

    public function writeCache(Request $request, array $content) {
        $this->cacheBo->setContent($request->getRequestTarget(), $content);
        $this->cacheBo->setExpires($request->getRequestTarget(), $this->times);
    }

    /**
     * @param int $hours
     * @return \App\Services\CacheService $this
     */
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

    public function object_2_json($object) {
        if ($object == null) {
            return '{}';
        }
        return json_encode($object->toArray());
    }

    public function object_2_array($object) {
        if ($object == null) {
            return [];
        }
        return json_decode($this->object_2_json($object), true);
    }
}