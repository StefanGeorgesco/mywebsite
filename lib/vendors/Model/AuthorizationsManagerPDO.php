<?php
namespace Model;

use \Entity\Authorization;

class AuthorizationsManagerPDO extends AuthorizationsManager
{
    protected function add(Authorization $authorization)
    {
        $q = $this->dao->prepare("
            INSERT INTO authorizations
            SET token=:token, hashPassToken=:hashPassToken,
            type=:type, member=:member, description=:description,
            creationDate=NOW(), updateDate=NOW()
        ");

        $q->bindValue(':token', $authorization->token());
        $q->bindValue(
            ':hashPassToken',
            password_hash($authorization->passToken(), PASSWORD_DEFAULT)
        );
        $q->bindValue(':type', $authorization->type());
        $q->bindValue(':member', $authorization->member());
        $q->bindValue(':description', $authorization->description());

        $ret = $q->execute();

        $authorization->setId($this->dao->lastInsertId());

        return $ret;
    }

    protected function modify(Authorization $authorization)
    {
        if (!$authorization->hasSameContent($this->get($authorization->id())))
        {
            $q = $this->dao->prepare("
                UPDATE authorizations
                SET description=:description,
                updateDate=NOW()
                WHERE id=:id
            ");

            $q->bindValue(':description', $authorization->description());
            $q->bindValue(':id', $authorization->id(), \PDO::PARAM_INT);

            return $q->execute();
        }

        return false;
    }

    protected function loadOpIds(Authorization $authorization)
    {
        $q = $this->dao->query("
        SELECT opid FROM opids WHERE authorization=
        ".$authorization->id());

        $opIds = array_map(
            function ($arr) { return $arr[0]; },
            array_values($q->fetchAll(\PDO::FETCH_NUM))
        );

        $authorization->setOpIds($opIds);

        return count($opIds);
    }

    public function addOpId(Authorization $authorization, string $opId)
    {
        $q = $this->dao->prepare("
        INSERT INTO opids SET opid=:opid, authorization=:authorization
        ");

        $q->bindValue(':opid', $opId);
        $q->bindValue(':authorization', $authorization->id(), \PDO::PARAM_INT);

        return $q->execute();
    }

    public function deleteOpIds(Authorization $authorization)
    {
        return $this->dao->exec("
            DELETE FROM opids WHERE authorization=
        ".$authorization->id());
    }

    public function delete(Authorization $authorization)
    {
        $res1 = $this->dao->exec("
            DELETE FROM opids WHERE authorization=
        ".$authorization->id());

        $res2 = $this->dao->exec("
            DELETE FROM authorizations WHERE id=
        ".$authorization->id());

        return $res1 + $res2;
    }

    public function deleteFromMember($member)
    {
        $res1 =  $this->dao->exec("
            DELETE FROM opids WHERE authorization IN
            (SELECT id FROM authorizations WHERE member=
        ".(int) $member.")
        ");

        $res2 =  $this->dao->exec("
            DELETE FROM authorizations WHERE member=
        ".(int) $member
        );

        return $res1 + $res2;
    }

    public function getListOfMember($member, $debut = -1, $limite = -1)
    {
        $sql = "
        SELECT id, token, hashPassToken,
        type, member, description,
        creationDate, updateDate
        FROM authorizations
        WHERE member=:member
        ORDER BY creationDate DESC
        ";

        if ($debut != -1 || $limite != -1)
        {
            $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
        }

        $q = $this->dao->prepare($sql);
        $q->bindValue(':member', $member, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Authorization'
        );

        $authorizations = $q->fetchAll();

        $q->closeCursor();

        foreach ($authorizations as $authorization)
        {
            $authorization->setCreationDate(
                new \DateTime($authorization->creationDate())
            );
            $authorization->setUpdateDate(
                new \DateTime($authorization->updateDate())
            );

            $this->loadOpIds($authorization);
        }

        return $authorizations;
    }

    public function getListOfAdmin($debut = -1, $limite = -1)
    {
        $sql = "
        SELECT id, token, hashPassToken,
        type, member, description,
        creationDate, updateDate
        FROM authorizations
        WHERE type='admin'
        ORDER BY creationDate DESC
        ";

        if ($debut != -1 || $limite != -1)
        {
            $sql .= ' LIMIT '.(int) $limite.' OFFSET '.(int) $debut;
        }

        $q = $this->dao->query($sql);

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Authorization'
        );

        $authorizations = $q->fetchAll();

        $q->closeCursor();

        foreach ($authorizations as $authorization)
        {
            $authorization->setCreationDate(
                new \DateTime($authorization->creationDate())
            );
            $authorization->setUpdateDate(
                new \DateTime($authorization->updateDate())
            );

            $this->loadOpIds($authorization);
        }

        return $authorizations;
    }

    public function get($id)
    {
        $q = $this->dao->prepare("
            SELECT id, token, hashPassToken,
            type, member, description,
            creationDate, updateDate
            FROM authorizations
            WHERE id=:id
        ");
        $q->bindValue(':id', (int) $id, \PDO::PARAM_INT);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Authorization'
        );

        if ($authorization = $q->fetch())
        {
            $authorization->setCreationDate(
                new \DateTime($authorization->creationDate())
            );
            $authorization->setUpdateDate(
                new \DateTime($authorization->updateDate())
            );

            $this->loadOpIds($authorization);

            return $authorization;
        }

        return null;
    }

    public function getByToken($token)
    {
        $q = $this->dao->prepare("
            SELECT id, token, hashPassToken,
            type, member, description,
            creationDate, updateDate
            FROM authorizations
            WHERE BINARY token=:token
        ");
        $q->bindValue(':token', $token);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Authorization'
        );

        if ($authorization = $q->fetch())
        {
            $authorization->setCreationDate(
                new \DateTime($authorization->creationDate())
            );
            $authorization->setUpdateDate(
                new \DateTime($authorization->updateDate())
            );

            $this->loadOpIds($authorization);

            return $authorization;
        }

        return null;
    }

    public function count($member='%')
    {
        $whereClause = $member == '' ? "type='admin'" : "member LIKE :member";
        $q = $this->dao->prepare("
            SELECT COUNT(*) FROM authorizations WHERE $whereClause
        ");
        $q->bindValue(':member', $member);
        $q->execute();

        return $q->fetchColumn();
    }
}
