<?php
namespace Model;

use \Entity\Authorization;

class AuthorizationsManagerPDO extends AuthorizationsManager
{
    protected function add(Authorization $authorization)
    {
        $q = $this->dao->prepare("
            INSERT INTO authorizations
            SET type=:type, memberId=:memberId,
            creationDate=NOW(), updateDate=NOW()
        ");

        $q->bindValue(':memberId', $authorization->memberId());
        $q->bindValue(':type', $authorization->type());

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
                SET type=:type, memberId=:memberId,
                updateDate=NOW()
                WHERE id=:id
            ");

            $q->bindValue(':type', $authorization->type());
            $q->bindValue(':memberId', $authorization->memberId());
            $q->bindValue(':id', $authorization->id(), \PDO::PARAM_INT);

            return $q->execute();
        }

        return false;
    }

    public function delete($id)
    {
        return $this->dao->exec("
            DELETE FROM authorizations WHERE id=
        ".(int) $id);
    }

    public function getList($debut = -1, $limite = -1)
    {
        $sql = "
        SELECT id, type, memberId, creationDate, updateDate
        FROM authorizations
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
        }

        return $authorizations;
    }

    public function get($id)
    {
        $q = $this->dao->prepare("
            SELECT id, type, memberId, creationDate, updateDate
            FROM authorizations
            WHERE id=:id
        ");
        $q->bindValue(':id', $id);
        $q->execute();

        $q->setFetchMode(
            \PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE,
            '\Entity\Authorization'
        );

        if ($authorization = $q->fetch())
        {
            $authorization->setMemberId((int) $authorization->memberId());
            $authorization->setCreationDate(
                new \DateTime($authorization->creationDate())
            );
            $authorization->setUpdateDate(
                new \DateTime($authorization->updateDate())
            );

            return $authorization;
        }

        return null;
    }

    public function count()
    {
        return $this->dao->query("
            SELECT COUNT(*) FROM authorizations
        ")->fetchColumn();
    }
}
