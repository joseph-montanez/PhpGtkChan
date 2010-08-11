<?php

namespace Chan;

interface Parser {

    /**
     * Get the section that the parser is evaluating
     * @return string
     */
    public function getSection();

    /**
     * Set the section of the parse to evaluate
     * @param string $section
     * @return Parser
     */
    public function setSection($section);

    /**
     * Get the section the parser is evaluating
     * @param string $section
     * @return Parser
     */
    public function getPages();

    /**
     * Get the all threads
     * @return Parser
     */
    public function getThreads();

}

?>