<?php

namespace Chan;

class Parser {

    public $pages = array();
    public $section = '';

    public function __construct($section = '') {
        $this->section = $section;
    }

    public function getSection() {
        return $this->section;
    }

    /**
     * Set the section of the parse to evaluate
     * @param string $section
     * @return Parser
     */
    public function setSection($section) {
        $this->section = $section;
        return $this;
    }

    /**
     * Get the section the parser is evaluating
     * @param string $section
     */
    public function getSectionPages($section) {
        $file = file_get_contents($section);

        $dom = new Gorilla3D\Dom($file);

        $this->pages = array($section . 'imgboard.html');
        foreach ($dom->body->getElements('//*[contains(@class, "pages")]//a') as $anchor) {
            array_push($this->pages, $section . $anchor->get('href'));
        }

        var_dump($pages);

        unset($dom);
        unset($file);
    }

}

?>