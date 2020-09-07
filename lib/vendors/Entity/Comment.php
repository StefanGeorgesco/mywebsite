<?php
namespace Entity;

use \OCFram\Entity;

class Comment extends Entity
{
    protected   $news,
                $member,
                $author,
                $contents,
                $creationDate,
                $updateDate;

const AUTHOR_INVALID = 1;
const CONTENTS_INVALID = 2;

    public function isValid()
    {
        return !(empty($this->contents));
    }

    public function hasSameContent(Comment $otherComment)
    {
        return $this->contents() == $otherComment->contents();
    }

    public function setNews($news)
    {
        $this->news = (int) $news;
    }

    public function setMember($member)
    {
        $this->member = (int) $member;
    }

    public function setAuthor($author)
    {
        if (!is_string($author) || empty($author))
        {
            $this->erreurs[] = self::AUTHOR_INVALID;
        }

        $this->author = $author;
    }

    public function setContents($contents)
    {
        if (!is_string($contents) || empty($contents))
        {
            $this->erreurs[] = self::CONTENTS_INVALID;
        }

        $this->contents = $contents;
    }

    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }

    public function setUpdateDate(\DateTime $updateDate)
    {
        $this->updateDate = $updateDate;
    }

    public function news()
    {
        return $this->news;
    }

    public function member()
    {
        return $this->member;
    }

    public function author()
    {
        return $this->author;
    }

    public function contents()
    {
        return $this->contents;
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
