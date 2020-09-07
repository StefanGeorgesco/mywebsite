<?php
namespace Entity;

use \OCFram\Entity;

class News extends Entity
{
    protected   $author,
                $title,
                $contents,
                $creationDate,
                $updateDate;

    const AUTHOR_INVALID = 1;
    const TITLE_INVALID = 2;
    const CONTENTS_INVALID = 3;

    public function isValid()
    {
        return !(
            empty($this->author)
            || empty($this->title)
            || empty($this->contents)
        );
    }

    public function hasSameContent(News $otherNews)
    {
        return $this->author() ==  $otherNews->author()
            && $this->title() ==  $otherNews->title()
            && $this->contents() == $otherNews->contents();
    }

    // SETTERS //

    public function setAuthor($author)
    {
        if (!is_string($author) || empty($author))
        {
            $this->erreurs[] = self::AUTHOR_INVALID;
        }

        $this->author = $author;
    }

    public function setTitle($title)
    {
        if (!is_string($title) || empty($title))
        {
            $this->erreurs[] = self::TITLE_INVALID;
        }

        $this->title = $title;
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

    // GETTERS //

    public function author()
    {
        return $this->author;
    }

    public function title()
    {
        return $this->title;
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
