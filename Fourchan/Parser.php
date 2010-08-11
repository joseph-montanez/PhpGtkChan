<?php
namespace Fourchan;

class Parser implements \Chan\Parser {

    public $pages = array();
    public $threads = array();
    public $section = '';

    public function __construct($section = '') {
        $this->section = $section;
    }

    public function getSection() {
        return $this->section;
    }

    public function setSection($section) {
        $this->section = $section;
        return $this;
    }

    public function getPages() {
        $cacheFile = 'data/' . md5($this->section) . '.html';
        if(is_file($cacheFile) === false) {
            $data = \file_get_contents($this->section);
            \file_put_contents($cacheFile, $data);
        } else {
            $data = \file_get_contents($data);
        }

        $dom = new \Gorilla3D\Dom($data);

        $this->pages = array($this->section . 'imgboard.html');
        foreach ($dom->body->getElements('//*[contains(@class, "pages")]//a') as $anchor) {
            array_push($this->pages, $this->section . $anchor->get('href'));
        }

        unset($dom);
        unset($file);

        return $this;
    }

    public function getThreads() {
        if(empty($this->pages)) {
            return $this;
        }

        foreach($this->pages as $page) {
            $cacheFile = 'data/' . md5($page) . '.html';
            if(is_file($cacheFile) === false) {
                $data = \file_get_contents($page);
                \file_put_contents($cacheFile, $data);
            } else {
                $data = \file_get_contents($data);
            }
            
            $dom = new \Gorilla3D\Dom($data);

            $anchors = $dom->body->getElements('//*[contains(@id, "nothread")]//*[contains(text(), "Reply")]');
            foreach($anchors as $anchor) {
                /** @var string The thread URL */
                $thread = $this->section . $anchor->get('href');
                \array_push($this->threads, $thread);
            }
        }
    }

    public function getThread() {
        //http://boards.4chan.org/w/res/1121158
    }
}
?>
