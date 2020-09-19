<?php
namespace OCFram;

abstract class Application
{
    protected $name;
    protected $httpRequest;
    protected $httpResponse;
    protected $user;
    protected $config;

    public function __construct()
    {
        $this->name = '';
        $this->httpRequest = new HTTPRequest($this);
        $this->httpResponse = new HTTPResponse($this);
        $this->user = new User($this);
        $this->config = new Config($this);
    }

    public function getController()
    {
        $router = new Router;

        $xml = new \DOMDocument;
        $xml->load(__DIR__.'/../../App/'.$this->name.'/Config/routes.xml');

        $routes = $xml->getElementsByTagName('route');

        foreach ($routes as $route)
        {
            $vars = [];

            if ($route->hasAttribute('vars'))
            {
                $vars = explode(',', $route->getAttribute('vars'));
            }

            $router->addRoute(
                new Route(
                    $route->getAttribute('url'),
                    $route->getAttribute('module'),
                    $route->getAttribute('action'),
                    $vars
                )
            );
        }

        try
        {
            $matchedRoute = $router->getRoute($this->httpRequest->requestURI());
        }
        catch (\RuntimeException $e)
        {
            if ($e->getCode() == Router::NO_ROUTE)
            {
                if ($this->name() == 'API')
                {
                    $this->httpResponse->jsonError(400);
                }
                else
                {
                    $this->httpResponse->redirect404();
                }
            }
        }

        $_GET = array_merge($_GET, $matchedRoute->vars());

        $controllerClass = 'App\\'.$this->name.'\\Modules\\'.
            $matchedRoute->module().'\\'.$matchedRoute->module().'Controller';

        return new $controllerClass(
            $this,
            $matchedRoute->module(),
            $matchedRoute->action()
        );
    }

    abstract public function run();

    public function name()
    {
        return $this->name;
    }

    public function httpRequest()
    {
        return $this->httpRequest;
    }

    public function httpResponse()
    {
        return $this->httpResponse;
    }

    public function user()
    {
        return $this->user;
    }

    public function config()
    {
        return $this->config;
    }

    public function HTTPProtocol()
    {
        return $_SERVER['SERVER_PORT'] == '80' ? 'http' : 'https';
    }

    public function host()
    {
        return $_SERVER['SERVER_NAME'];
    }

    public function baseUrl()
    {
        return $this->HTTPProtocol().'://'.$this->host().'/';
    }
}
