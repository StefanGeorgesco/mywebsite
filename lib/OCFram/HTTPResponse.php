<?php
namespace OCFram;

class HTTPResponse extends ApplicationComponent
{
    protected $page;
    protected $json;

    public function addHeader($header)
    {
        header($header);
    }

    public function redirect($location)
    {
        header('Location: '.$location);
        exit;
    }

    public function redirect404()
    {
        $this->page = new Page($this->app);
        $this->page->setContentFile(__DIR__.'/../../Errors/404.html');

        $this->addHeader('HTTP/1.0 404 Not Found');

        $this->send();
    }

    public function jsonError400()
    {
        $this->addHeader("HTTP/1.0 400 Bad Request");

        $response = array(
            'message' => 'Bad Request',
        );

        $this->setJson(json_encode($response));

        $this->sendJson();
    }

    public function jsonError401()
    {
        $this->addHeader("HTTP/1.0 401 Unauthorized");

        $response = array(
            'message' => 'Unauthorized',
        );

        $this->setJson(json_encode($response));

        $this->sendJson();
    }

    public function jsonError404()
    {
        $this->addHeader("HTTP/1.0 404 Not Found");

        $response = array(
            'message' => 'Not Found',
        );

        $this->setJson(json_encode($response));

        $this->sendJson();
    }

    public function send()
    {
        exit($this->page->getGeneratedPage());
    }

    public function sendJson()
    {
        exit($this->json());
    }

    public function json()
    {
        return $this->json;
    }

    public function setPage(Page $page)
    {
        $this->page = $page;
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

    public function setCookie(
        $name,
        $value = '',
        $expire = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
        )
        {
            setcookie($name, $value, $expire, $path, $domain, $secure, $httpOnly);
        }
    }
