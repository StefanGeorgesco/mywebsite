<?php
namespace OCFram;

trait PageUtils
{
    public function autoVersion($file)
    {
      if(!file_exists($_SERVER['DOCUMENT_ROOT'] . $file))
        return $file;

      $mtime = filemtime($_SERVER['DOCUMENT_ROOT'] . $file);
      return preg_replace("#\.([^./]+)$#", ".$mtime.$1", $file);
    }

    public function frenchDate($dateString)
    {
        if (empty($dateString))
        {
            return '';
        }

        return (new \DateTime($dateString))->format('d/m/Y');
    }

    public function parseString($s)
    {
        $myColors = array(
            'red',
            'green',
            'blue',
            'yellow',
            'magenta',
            'cyan',
            'purple',
            'pink',
            'olive',
            'white'
        );
        $myTags = array(
            'p','h[1-6]', 'div', 'span', 'body', 'html', 'head', 'title',
            'script', 'main', 'header', 'footer', 'nav', 'aside', 'article',
            'section', 'b', 'strong', 'u', 'i', 'em', 'ul', 'ol', 'li'
        );
        return nl2br(
            preg_replace(
                array(
                    '#\[b\](.+)\[/b\]#isU',
                    '#\[i\](.+)\[/i\]#isU',
                    '#\[em\](.+)\[/em\]#isU',
                    '#\[u\](.+)\[/u\]#isU',
                    '#\[color=('.implode('|', $myColors).')\](.+)\[/color\]#isU',
                    '#https?://\S+#i',
                    '#\S+@\S+#i',
                    '#\[img\](\S+[0-9a-z_/-])\[/img\]#iU',
                    '#&lt;(/?('.implode('|', $myTags).'))&gt;#iU'
                ),
                array(
                    '<b>$1</b>',
                    '<i>$1</i>',
                    '<em>$1</em>',
                    '<u>$1</u>',
                    '<span style="color: $1">$2</span>',
                    '<a href="$0" target="blank">$0</a>',
                    '<a href="mailto:$0">$0</a>',
                    '<img src="$1" />',
                    '<span style="color : blue;">&lt;$1&gt;</span>'
                ),
                htmlspecialchars($s)
            )
        );
    }
}
