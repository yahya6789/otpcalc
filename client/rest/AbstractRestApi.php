<?php

namespace otpcalc\client\rest;

defined('DOCUMENT_ROOT') OR exit('No direct script access allowed');

/**
 * @author http://coreymaynard.com/blog/creating-a-restful-api-with-php/
 *
 * Modifications:
 *
 * Yahya <yahya6789@gmail.com>
 *
 */

abstract class AbstractRestApi
{
    protected $method   = '';

    protected $endpoint = '';

    protected $verb     = '';

    protected $args     = array();

    protected $file     = NULL;

    public function __construct($request)
    {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);

        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0]))
        {
            $this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];

        switch($this->method)
        {
            case 'GET':
                $this->request = $this->cleanInput($_GET);
                break;

            default:
                $this->setResponse('Unsupported HTTP method: ' . $this->method, 405);
                break;
        }
    }

    public function process()
    {
        if (method_exists($this, $this->endpoint))
        {
            return $this->setResponse($this->{$this->endpoint}($this->args));
        }

        return $this->setResponse("No Endpoint: $this->endpoint", 404);
    }

    private function setResponse($data, $status = 200)
    {
        header("HTTP/1.1 " . $status . " " . $this->getRequestStatus($status));

        if($data)
        {
            return json_encode($data);
        }
    }

    private function cleanInput($data)
    {
        $clean_input = Array();
        if (is_array($data))
        {
            foreach ($data as $k => $v)
            {
                $clean_input[$k] = $this->cleanInput($v);
            }
        }
        else
        {
            $clean_input = trim(strip_tags($data));
        }

        return $clean_input;
    }

    private function getRequestStatus($code)
    {
        $status = array(
            200 => 'OK',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            500 => 'Internal Server Error',
        );

        return ($status[$code])?$status[$code]:$status[500];
    }
} 