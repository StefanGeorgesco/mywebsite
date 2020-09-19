<?php
namespace OCFram;

use \Entity\Authorization;

abstract class APIController extends Controller
{
    protected $json = '';

    protected function getAuthorization()
    {
        $authorization = $this->managers->getManagerOf('Authorizations')
            ->get($this->app->httpRequest()->authorizationToken());

        if (!$authorization && $this->app->user()->isAuthenticated())
        {
            $member = $this->managers->getManagerOf('Members')
                ->getByLogin($this->app->user()->getAttribute('login'));

            $authorization = new Authorization(
                [
                    'type' => 'member',
                    'memberId' => $member->id()
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
        return $this->json;
    }

    public function setJson($json)
    {
        if (!is_string($json) || empty($json))
        {
            throw new \InvalidArgumentException(
                'Le json doit être une chaine de caractères valide'
            );
        }

        $this->json = $json;
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
