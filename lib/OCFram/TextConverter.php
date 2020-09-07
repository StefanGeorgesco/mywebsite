<?php
namespace OCFram;

trait TextConverter
{
    public function convertedText($text)
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
            'p','h[1-6]', 'div', 'span', 'body', 'html', 'head', 'title', 'script',
            'main', 'header', 'footer', 'nav', 'aside', 'article', 'section',
            'b', 'strong', 'u', 'i', 'em'
        );
        return preg_replace(
            array(
                '#\[b\](.+)\[/b\]#isU',
                '#\[i\](.+)\[/i\]#isU',
                '#\[em\](.+)\[/em\]#isU',
                '#\[u\](.+)\[/u\]#isU',
                '#\[color=(' . implode('|', $myColors) . ')\](.+)\[/color\]#isU',
                '#https?://\S+[0-9a-z_/-](\?\S+[0-9a-z_/-])?#i',
                '#\S+[0-9a-z_/-]@\S+[0-9a-z_/-]#i',
                '#\[img\](\S+[0-9a-z_/-])\[/img\]#iU',
                '#&lt;(/?(' . implode('|', $myTags) . '))&gt;#iU'
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
            nl2br(htmlspecialchars($text))
        );
    }
}
