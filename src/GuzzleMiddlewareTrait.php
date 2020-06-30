<?php

namespace Shabbir\GuzzleMiddlewareStack;

use GuzzleHttp\Handler\CurlHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait GuzzleMiddlewareTrait
{
    /**
     * @param $class_name
     * @param $method_name
     * @return HandlerStack
     */
    public static function initialize(&$log){
        $handler = new CurlHandler();
        $stack = HandlerStack::create($handler);
        $start_time = 0;

        // request middleware
        $stack->push(Middleware::mapRequest(function (Request $request) use (&$log, &$start_time) {
            $headers = $request->getHeaders();
            $log['request'] = [
                'uri' => (string) $request->getUri(),
                'method' => $request->getMethod(),
                'body' => urldecode($request->getBody()->getContents()),
                'headers' => json_encode($headers)
            ];
            $start_time = microtime_int();
            return $request;
        }));

        // response middleware
        $stack->push(Middleware::mapResponse(function (Response $response) use (&$log, &$start_time){
            $body = $response->getBody()->getContents();
            $jsonBody = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $jsonBody = $body;
            }
            $log['response'] = [
                'body' => $jsonBody,
                'status' => $response->getStatusCode(),
            ];
            $log['start_time'] = convertToDateTimeString($start_time);
            $log['end_time'] = convertToDateTimeString($end_time = microtime_int());
            $log['total_time']= $end_time - $start_time;

            if ($response->getBody()->isSeekable())
            {
                $response->getBody()->rewind();
            }
            return $response;
        }));

        return $stack;
    }

}