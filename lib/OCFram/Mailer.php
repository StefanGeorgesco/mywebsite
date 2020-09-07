<?php
namespace OCFram;

class Mailer extends ApplicationComponent
{
    protected $contentFile;
    protected $to;
    protected $subject;
    protected $message;
    protected $vars = [];

    const HEADERS = "Content-type: text/html; charset=UTF-8";

    public function addVar($var, $value)
    {
        if (!is_string($var) || is_numeric($var) || empty($var))
        {
            throw new \InvalidArgumentException(
                'Le nom de la variable doit être une chaine de caractères non nulle'
            );
        }

        $this->vars[$var] = $value;
    }

    protected function generateMessage()
    {
        if (!file_exists($this->contentFile))
        {
            throw new \RuntimeException('Le modèle spécifié n\'existe pas');
        }

        $host = $this->app->config()->get('host_domain');
        extract($this->vars);

        ob_start();
        require $this->contentFile;
        $this->setMessage(ob_get_clean());
    }

    public function send()
	{
        $this->generateMessage();
		//mail($this->to, $this->subject, $this->message, self::HEADERS);
        // FOR DEVELOPMENT PHASE
        file_put_contents('/home/stefan/email.html', $this->message);
	}

    // SETTERS

    public function setContentFile($contentFile)
    {
        if (!is_string($contentFile) || empty($contentFile))
        {
            throw new \InvalidArgumentException('Le modèle spécifié est invalide');
        }

        $this->contentFile = $contentFile;
    }

    public function setTo($to)
    {
        if (!is_string($to) || empty($to))
        {
            throw new \InvalidArgumentException('Le modèle spécifié est invalide');
        }

        $this->to = $to;
    }

    public function setSubject($subject)
    {
        if (!is_string($subject) || empty($subject))
        {
            throw new \InvalidArgumentException('Le modèle spécifié est invalide');
        }

        $this->subject = $subject;
    }

    public function setMessage($message)
    {
        if (!is_string($message) || empty($message))
        {
            throw new \InvalidArgumentException('Le modèle spécifié est invalide');
        }

        $this->message = $message;
    }
}
