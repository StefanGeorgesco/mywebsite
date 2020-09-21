<?php
namespace Entity;

use \OCFram\Entity;

class Authorization extends Entity
{
    use \OCFram\RandomToken;

    protected   $token,
                $passToken,
                $hashPassToken,
                $type,
                $member = null,
                $description,
                $creationDate,
                $updateDate;

    const TYPES = ['admin', 'member'];

    const TOKEN_INVALID = 1;
    const PASSTOKEN_INVALID = 2;
    const TYPE_INVALID = 3;
    const MEMBERID_INVALID = 4;
    const DESCRIPTION_INVALID = 5;

    public function __construct(array $donnees = [])
    {
        parent::__construct($donnees);

        $this->setToken($this->randomToken(16));
        $this->setPassToken($this->randomToken(16));
    }

    public function isValid()
    {
        return !(
            empty($this->type)
            || $this->type == 'member' && is_null($this->member)
        );
    }

    public function hasSameContent(Comment $otherAuthorization)
    {
        return $this->description() == $otherAuthorization->description();
    }

    public function isAdmin()
    {
        return $this->type == 'admin';
    }

    public function isMember()
    {
        return $this->type == 'member';
    }

    public function getFullToken()
    {
        return $this->token() . $this->passToken();
    }

    // SETTERS //

    public function setToken($token)
    {
        if (!is_string($token))
        {
            $this->erreurs[] = self::TOKEN_INVALID;
        }

        $this->token = $token;
    }

    public function setPassToken($passToken)
    {
        if (!is_string($passToken))
        {
            $this->erreurs[] = self::PASSTOKEN_INVALID;
        }

        $this->passToken = $passToken;
    }

    public function setType($type)
    {
        if (!in_array($type, self::TYPES))
        {
            $this->erreurs[] = self::TYPE_INVALID;
        }

        $this->type = $type;
    }

    public function setMember($member)
    {
        if (!is_numeric($member) || ((int) $member) < 0)
        {
            $this->erreurs[] = self::MEMBERID_INVALID;
        }

        $this->member = (int) $member;
    }

    public function setDescription($description)
    {
        if (!is_string($description))
        {
            $this->erreurs[] = self::DESCRIPTION_INVALID;
        }

        $this->description = $description;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;
    }

    // GETTERS //

    public function token()
    {
        return $this->token;
    }

    public function passToken()
    {
        return $this->passToken;
    }

    public function hashPassToken()
    {
        return $this->hashPassToken;
    }

    public function type()
    {
        return $this->type;
    }

    public function member()
    {
        return $this->member;
    }

    public function description()
    {
        return $this->description;
    }

    public function creationDate()
    {
        return $this->creationDate;
    }

    public function updateDate()
    {
        return $this->updateDate;
    }
}
