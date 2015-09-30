<?php

namespace otpcalc\client\rest;

defined('DOCUMENT_ROOT') OR exit('No direct script access allowed');

use otpcalc\domain\Hotp;

/**
 * REST controller (end point)
 *
 * @author Yahya <yahya6789@gmail.com>
 */

class RestApi extends AbstractRestApi
{
    public function __construct($request)
    {
        parent::__construct($request);
    }

    public function hotp()
    {
        if(!$this->is_method_allowed())
        {
            throw new \Exception("HTTP method not allowed: " . $this->method);
        }

        $key        = $this->search('key');
        $length     = $this->search('length', 6);
        $counter    = $this->search('counter');

        $hotp = new Hotp();
        $otp = $hotp->generateCounterBasedOtp($key, $counter, $length);
        return array('error' => FALSE, 'message' => $otp);
    }

    public function totp()
    {
        if(!$this->is_method_allowed())
        {
            throw new \Exception("HTTP method not allowed: " . $this->method);
        }

        $key        = $this->search('key');
        $length     = $this->search('length', 6);
        $time       = $this->search('time');
        $timestep   = $this->search('timestep', 30);

        $hotp = new Hotp();
        $otp = $hotp->generateTimeBasedOtp($key, $time, $timestep, $length);
        return array('error' => FALSE, 'message' => $otp);
    }

    private function is_method_allowed()
    {
        return $this->method == 'GET';
    }

    private function search($key, $default = NULL)
    {
        $haystack = explode('/', $this->request['request']);
        $index = array_search($key, $haystack);
        if($index)
        {
            if(array_key_exists(($index + 1), $haystack))
            {
                return $haystack [$index + 1];
            }
            else
            {
                throw new \Exception('parameter value not found: ' . $key);
            }
        }
        else
        {
            if(is_null($default))
            {
                throw new \Exception('parameter name not found: ' . $key);
            }
            else
            {
                return $default;
            }
        }
    }
} 