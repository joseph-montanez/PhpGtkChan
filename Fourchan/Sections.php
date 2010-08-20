<?php
namespace Fourchan;

use Gorilla3D;


class Sections {
    /** @var array */
    public static $sections = array(
        '/w/ - Anime/Wallpapers' => 'http://boards.4chan.org/w/',
        '/wg/ - Wallpapers/General' => 'http://boards.4chan.org/wg/'
    );
    public static function getSections() {
        return self::$sections;
    }
}

?>
