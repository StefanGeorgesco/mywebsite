<?php
namespace Model;

use \Entity\Member;

class MembersManagerPDO extends MembersManager
{
    const TOKEN_VALIDITY_TIME = 24 * 60 * 60;

    protected function add(Member $member)
    {
        $login = $member->login();
        $email = $member->email();

        if (!$this->existsLogin($login) && !$this->existsEmail($email))
        {
            $q = $this->dao->prepare("
                INSERT INTO members
                SET login=:login, pass=:pass, email=:email,
                firstName=:firstName, lastName=:lastName, birthDate=:birthDate,
                phone=:phone, website=:website, housenumber=:housenumber,
                street=:street, postcode=:postcode, city=:city,
                token=:token, tokenExpiryTime=:tokenExpiryTime,
                creationDate=NOW(), updateDate=NOW(),
                active=0
            ");

            $token = $this->randomToken();
            $tokenExpiryTime = date(
                'Y-m-d H:i:s',
                time() + self::TOKEN_VALIDITY_TIME
            );

            $q->bindValue(':login', $member->login());
            $q->bindValue(
                ':pass',
                password_hash($member->pass(), PASSWORD_DEFAULT)
            );
            $q->bindValue(':email', $member->email());
            $q->bindValue(':firstName', $member->firstName());
            $q->bindValue(':lastName', $member->lastName());
            $q->bindValue(
                ':birthDate',
                $member->birthDate() ?
                $member->birthDate()->format('Y-m-d') :
                null
            );
            $q->bindValue(':phone', $member->phone());
            $q->bindValue(':website', $member->website());
            $q->bindValue(':housenumber', $member->housenumber());
            $q->bindValue(':street', $member->street());
            $q->bindValue(':postcode', $member->postcode());
            $q->bindValue(':city', $member->city());
            $q->bindValue(':token', $token);
            $q->bindValue(':tokenExpiryTime', $tokenExpiryTime);

            $ret = $q->execute();

            $member->setId($this->dao->lastInsertId());
            $member->setToken($token);
            $member->setTokenExpiryTime(new \DateTime($tokenExpiryTime));

            return $ret;
        }

        return false;
    }

    protected function modify(Member $member)
    {
        $login = $member->login();
        $email = $member->email();
        $storedMember = $this->getById($member->id());

        if (
            !$member->hasSameContent($storedMember)
            && (
                !$this->existsLogin($login)
                || strtolower($login) == strtolower($storedMember->login())
                )
            && (
                !$this->existsEmail($email)
                || strtolower($email) == strtolower($storedMember->email())
                )
            )
        {
            $q = $this->dao->prepare("
                UPDATE members
                SET login=:login, email=:email,
                firstName=:firstName, lastName=:lastName, birthDate=:birthDate,
                phone=:phone, website=:website,  housenumber=:housenumber,
                street=:street, postcode=:postcode, city=:city,
                updateDate=NOW()
                WHERE id=:id
            ");

            $q->bindValue(':login', $member->login());
            $q->bindValue(':email', $member->email());
            $q->bindValue(':firstName', $member->firstName());
            $q->bindValue(':lastName', $member->lastName());
            $q->bindValue(
                ':birthDate',
                $member->birthDate() ?
                $member->birthDate()->format('Y-m-d') :
                null
            );
            $q->bindValue(':phone', $member->phone());
            $q->bindValue(':website', $member->website());
            $q->bindValue(':housenumber', $member->housenumber());
            $q->bindValue(':street', $member->street());
            $q->bindValue(':postcode', $member->postcode());
            $q->bindValue(':city', $member->city());
            $q->bindValue(':id', $member->id(), \PDO::PARAM_INT);

            return $q->execute();
        }

        return false;
    }

    public function generateToken(Member $member)
    {
        $token = $this->randomToken();
        $tokenExpiryTime = date(
            'Y-m-d H:i:s',
            time() + self::TOKEN_VALIDITY_TIME
        );

        $q = $this->dao->prepare("
            UPDATE members
            SET token=:token, tokenExpiryTime=:tokenExpiryTime
            WHERE email=:email
        ");

        $q->bindValue(':token', $token);
        $q->bindValue(':tokenExpiryTime', $tokenExpiryTime);
        $q->bindValue(':email', $member->email());

        if ($q->execute()) return $token;

        return null;
    }

    public function inhibitToken(Member $member)
    {
        $q = $this->dao->prepare("
            UPDATE members
            SET tokenExpiryTime=:tokenExpiryTime
            WHERE id=:id
        ");

        $q->bindValue(
            ':tokenExpiryTime',
            date('Y-m-d H:i:s', time())
        );
        $q->bindValue(':id', $member->id(), \PDO::PARAM_INT);

        return $q->execute();
    }

    public function activate(Member $member)
    {
        $q = $this->dao->prepare("
            UPDATE members
            SET active=1, tokenExpiryTime=:tokenExpiryTime
            WHERE id=:id
        ");

        $q->bindValue(
            ':tokenExpiryTime',
            date('Y-m-d H:i:s', time())
        );
        $q->bindValue(':id', $member->id(), \PDO::PARAM_INT);

        return $q->execute();
    }

    public function modifyPassword(Member $member)
    {
        $q = $this->dao->prepare("
            UPDATE members
            SET pass=:pass
            WHERE id=:id
        ");

        $hashPass = password_hash($member->pass(), PASSWORD_DEFAULT);

        $q->bindValue(':pass', $hashPass);
        $q->bindValue(':id', $member->id(), \PDO::PARAM_INT);

        $ret = $q->execute();

        $member->setHashPass($hashPass);

        return $ret;
    }

    public function delete(Member $member)
    {
        return $this->dao->exec("
            DELETE FROM members WHERE id=
        ".$member->id());
    }

    public function getList($debut = -1, $limite = -1, $activeOnly=true)
    {
        $whereClause = $activeOnly ? 'active=1' : '1';

        $sql = "
        SELECT id, login, email, firstName, lastName, birthDate, phone, website,
        housenumber, street, postcode, city,
        creationDate, updateDate, active
        FROM members
        WHERE $whereClause
        ORDER BY login
        ";

        if ($debut != -1 || $limite != -1)
        {
            $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
        }

        $q = $this->dao->query($sql);
        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Member'
        );

        $members = $q->fetchAll();

        $q->closeCursor();

        foreach ($members as $member)
        {
            if ($member->birthDate())
            {
                $member->setBirthDate(new \DateTime($member->birthDate()));
            }
            $member->setCreationDate(new \DateTime($member->creationDate()));
            $member->setUpdateDate(new \DateTime($member->updateDate()));
            $member->setActive((bool) $member->active());
        }

        return $members;
    }

    public function getById($id)
    {
        $q = $this->dao->prepare("
            SELECT id, login, email, firstName, lastName,
            birthDate, phone, website,
            housenumber, street, postcode, city,
            creationDate, updateDate, active
            FROM members
            WHERE id=:id
        ");
        $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Member'
        );

        if ($member = $q->fetch())
        {
            if ($member->birthDate())
            {
                $member->setBirthDate(new \DateTime($member->birthDate()));
            }
            $member->setCreationDate(new \DateTime($member->creationDate()));
            $member->setUpdateDate(new \DateTime($member->updateDate()));
            $member->setActive((bool) $member->active());

            return $member;
        }

        return null;
    }

    public function getByLogin($login)
    {
        $q = $this->dao->prepare("
            SELECT id, login, pass AS hashPass, email,
            firstName, lastName, birthDate, phone, website,
            housenumber, street, postcode, city,
            creationDate, updateDate, active, tokenExpiryTime
            FROM members
            WHERE BINARY login=:login
        ");
        $q->bindValue(':login', $login);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Member'
        );

        if ($member = $q->fetch())
        {
            if ($member->birthDate())
            {
                $member->setBirthDate(new \DateTime($member->birthDate()));
            }
            $member->setCreationDate(new \DateTime($member->creationDate()));
            $member->setUpdateDate(new \DateTime($member->updateDate()));
            $member->setActive((bool) $member->active());
            $member->setTokenExpiryTime(
                new \DateTime($member->tokenExpiryTime())
            );

            return $member;
        }

        return null;
    }

    public function getByEmail($email)
    {
        $q = $this->dao->prepare("
            SELECT id, login, pass AS hashPass, email,
            firstName, lastName, birthDate, phone, website,
            housenumber, street, postcode, city,
            creationDate, updateDate, active, tokenExpiryTime
            FROM members
            WHERE email=:email
        ");
        $q->bindValue(':email', $email);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Member'
        );

        if ($member = $q->fetch())
        {
            if ($member->birthDate())
            {
                $member->setBirthDate(new \DateTime($member->birthDate()));
            }
            $member->setCreationDate(new \DateTime($member->creationDate()));
            $member->setUpdateDate(new \DateTime($member->updateDate()));
            $member->setActive((bool) $member->active());
            $member->setTokenExpiryTime(
                new \DateTime($member->tokenExpiryTime())
            );

            return $member;
        }

        return null;
    }

    public function getByToken($token)
    {
        $q = $this->dao->prepare("
            SELECT id, login, pass AS hashPass, email,
            firstName, lastName, birthDate, phone, website,
            housenumber, street, postcode, city,
            creationDate, updateDate, active, tokenExpiryTime
            FROM members
            WHERE BINARY token=:token
        ");
        $q->bindValue(':token', $token);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Member'
        );

        if ($member = $q->fetch())
        {
            if ($member->birthDate())
            {
                $member->setBirthDate(new \DateTime($member->birthDate()));
            }
            $member->setCreationDate(new \DateTime($member->creationDate()));
            $member->setUpdateDate(new \DateTime($member->updateDate()));
            $member->setActive((bool) $member->active());
            $member->setTokenExpiryTime(
                new \DateTime($member->tokenExpiryTime())
            );

            return $member;
        }

        return null;
    }

    public function existsLogin($login)
    {
        $q = $this->dao->prepare("
            SELECT COUNT(*) FROM members
            WHERE login=:login
        ");
        $q->bindValue(':login', $login);
        $q->execute();

        return $q->fetchColumn() > 0;
    }

    public function existsEmail($email)
    {
        $q = $this->dao->prepare("
            SELECT COUNT(*) FROM members
            WHERE email=:email
        ");
        $q->bindValue(':email', $email);
        $q->execute();

        return $q->fetchColumn() > 0;
    }

    public function count($activeOnly=true)
    {
        $whereClause = $activeOnly ? 'active=1' : '1';

        return $this->dao->query("
            SELECT COUNT(*) FROM members
            WHERE $whereClause
        ")->fetchColumn();
    }
}
