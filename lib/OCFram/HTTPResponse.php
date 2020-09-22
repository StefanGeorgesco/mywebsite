<?php
namespace OCFram;

class HTTPResponse extends ApplicationComponent
{
    protected $page;
    protected $json;

    const HTTP_RESPONSES = [
        '400' => 'Bad Request',
        '401' => 'Unauthorized',
        '404' => 'Not Found'
    ];

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

    public function jsonError($code, string $details = '')
    {
        $code = (string) $code;
        $message = self::HTTP_RESPONSES[$code];

        $this->addHeader("HTTP/1.0 $code $message");

        $response = array(
            'message' => $message,
        );

        if ($details)
        {
            $response['details'] = $details;
        }

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
