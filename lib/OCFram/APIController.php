<?php
namespace OCFram;

use \Entity\Authorization;

abstract class APIController extends Controller
{
    protected $response;

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

    public function json()
    {
        return json_encode(
            $this->response(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
    }

    public function response()
    {
        return $this->response;
    }

    public function setResponse($response)
    {
        $this->response = $response;
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
}
