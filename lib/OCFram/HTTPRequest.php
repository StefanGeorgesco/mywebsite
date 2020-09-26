<?php
namespace OCFram;

class HTTPRequest extends ApplicationComponent
{
    public function cookieData($key)
    {
        return isset($_COOKIE[$key]) ? $_COOKIE[$key] : null;
    }

    public function cookieExists($key)
    {
        return isset($_COOKIE[$key]);
    }

    public function getData($key)
    {
        return isset($_GET[$key]) ? $_GET[$key] : null;
    }

    public function getExists($key)
    {
        return isset($_GET[$key]);
    }

    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function postData($key)
    {
        return isset($_POST[$key]) ? $_POST[$key] : null;
    }

    public function postExists($key)
    {
        return isset($_POST[$key]);
    }

    public function requestURI()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function params()
    {
        return $_GET;
    }

    public function requestBodyData()
    {
        $data = json_decode(file_get_contents('php://input') , true);

        if (is_null($data))
        {
            throw new \RuntimeException("bad json request body data", 1);

        }

        return $data;
    }

    public function requestBodyDataVar(string $var)
    {
        $data = json_decode(file_get_contents('php://input') , true);

        if (!is_null($data) && isset($data[$var]))
        {
            return $data[$var];
        }

        return null;
    }

    public function authorizationToken()
    {
        $token = null;

        $headers = apache_request_headers();

        if(isset($headers['Authorization']))
        {
            $matches = array();

            preg_match("#^token (\S+)$#", $headers['Authorization'], $matches);

            if(isset($matches[1]))
            {
                $token = $matches[1];
            }
        }

        return $token;
    }
}
