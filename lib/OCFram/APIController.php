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
            $value = $property->getValue($object);
            $array[$property->getName()] = $value !== null ? $value : "";
            $property->setAccessible(false);
        }

        return $array;
    }

    protected function filter(array $array)
    {
        if (!($params = $this->app->httpRequest()->params()))
        {
            return $array;
        }

        function matches($testValue, $subject)
        {
            if ($subject instanceof \DateTime)
            {
                $subject = $subject->format('Y-m-d');
            }

            return preg_match(
                '#' . strtolower($testValue) . '#',
                strtolower($subject)
            );
        }

        $testArray = function ($arr, $key) use ($params)
        {
        	foreach ($params as $testKey => $testValue)
        	{
        		if (isset($arr[$testKey]) &&
                    !matches($testValue, $arr[$testKey]))
        		{
        			return false;
        		}
        	}

        	return true;
        };

        $array = array_values(
            array_filter(
                $array,
                $testArray,
                ARRAY_FILTER_USE_BOTH
            )
        );

        return $array;
    }

    public function paginate(array $array, $itemsPerPage = 20)
    {
        $params = $this->app->httpRequest()->params();

        if (!isset($params['page']))
        {
            return $array;
        }

        $page = $params['page'];
        $count = count($array);
        $numberOfPages = max(1, (int) ceil($count / $itemsPerPage));

        if (!is_numeric($page) || $page < 1 || $page > $numberOfPages)
        {
            throw new \RuntimeException("this page does not exist", 1);
        }

        $offset = ($page - 1) * $itemsPerPage;

        return array_slice($array, $offset, $itemsPerPage);
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
