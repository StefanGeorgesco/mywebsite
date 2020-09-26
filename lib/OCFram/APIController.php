<?php
namespace OCFram;

use \Entity\Authorization;
use \OCFram\HTTPResponse;

abstract class APIController extends Controller
{
    protected $response;
    protected $headers = [];

    protected function getAuthorization()
    {
        $authorizationToken = $this->app->httpRequest()->authorizationToken();
        $token = substr($authorizationToken, 0, 32);
        $passToken = substr($authorizationToken, 32);

        $authorization = $this->managers->getManagerOf('Authorizations')
            ->getByToken($token);

        if ($authorization &&
            !password_verify($passToken, $authorization->hashPassToken()))
        {
            $authorization = null;
        }

        if (!$authorization && $this->app->user()->isAuthenticated())
        {
            $member = $this->managers->getManagerOf('Members')
                ->getByLogin($this->app->user()->getAttribute('login'));

            $authorization = new Authorization(
                [
                    'type' => 'member',
                    'member' => $member->id()
                ]
            );
        }

        if (!$authorization && $this->app->user()->isAdmin())
        {
            $authorization = new Authorization(['type' => 'admin']);
        }

        return $authorization;
    }

    protected function dismount($object) {
        $reflectionClass = new \ReflectionClass(get_class($object));

        $array = array();

        foreach ($reflectionClass->getProperties() as $property) {
            $property->setAccessible(true);
            $array[$property->getName()] = $property->getValue($object);
            $property->setAccessible(false);
        }

        return $array;
    }

    protected function filter(array $array)
    {
        $params = $this->app->httpRequest()->params();

        $testArray = function ($arr, $key) use ($params)
        {
        	foreach ($params as $testKey => $testValue)
        	{
        		if (isset($arr[$testKey]) && $arr[$testKey] !== $testValue)
        		{
        			return false;
        		}
        	}

        	return true;
        };

        return array_values(
            array_filter(
                $array,
                $testArray,
                ARRAY_FILTER_USE_BOTH
            )
        );
    }

    public function addHeader($header)
    {
        $this->headers[] = $header;
    }

    public function setResponseCode($code)
    {
        $code = (string) $code;
        $message = HTTPResponse::HTTP_RESPONSES[$code];

        $this->addHeader("HTTP/1.1 $code $message");
    }

    public function exitWithError($code, string $details = '', array $errors = [])
    {
        $this->app->httpResponse()->jsonError($code, $details, $errors);
    }

    public function json()
    {
        return json_encode(
            $this->response(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    // GETTERS

    public function headers()
    {
        return $this->headers;
    }

    public function response()
    {
        return $this->response;
    }

    // SETTERS

    public function setResponse($response)
    {
        $this->response = $response;
    }
}
