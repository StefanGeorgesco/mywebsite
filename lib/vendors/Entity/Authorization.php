<?php
namespace Entity;

use \OCFram\Entity;

class Authorization extends Entity
{
    protected   $type,
                $memberId = null,
                $creationDate,
                $updateDate;

    const TYPES = ['admin', 'member'];
    const TYPE_INVALID = 1;
    const MEMBER_ID_INVALID = 2;

    public function isValid()
    {
        return !(
            empty($this->type)
            || $this->type == 'member' && is_null($this->memberId)
        );
    }

    public function isAdmin()
    {
        return $this->type == 'admin';
    }

    public function isMember()
    {
        return $this->type == 'member';
    }

    // SETTERS //

    public function setType($type)
    {
        if (!in_array($type, self::TYPES))
        {
            $this->erreurs[] = self::TYPE_INVALID;
        }

        $this->type = $type;
    }

    public function setMemberId($memberId)
    {
        if (!is_numeric($memberId) || ((int) $memberId) < 0)
        {
            $this->erreurs[] = self::MEMBER_ID_INVALID;
        }

        $this->memberId = (int) $memberId;
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

    public function type()
    {
        return $this->type;
    }

    public function memberId()
    {
        return $this->memberId;
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
