<?php
/**
 * File docblock description
 *
 * Easier api to DOM
 * @author Joseph Montanez <sutabi@gmail.com>
 * @version 0.0.4
 * @package Dom
 */

namespace Gorilla3D;

/**
 * @package Dom
 */
class Dom 
{
    function __construct($filename = null) 
    {
        $this->html = new \DOMDocument();
        $this->html->formatOutput = true;
        if(is_file($filename)) {
            @$this->html->loadHTMLFile($filename);
        } else if($filename !== null) {
            @$this->html->loadHTML($filename);
        }
    }

    public function element($tagname, array $properties = array()) 
    {
        $element = $this->evalHtml('<' . $tagname . '> </' . $tagname . '>');
        if($empty($properties))
        {
            foreach($properties as $key => $value)
            {
                $element->set($key, $value);
            }
        }
        return $element;
    }


    /**
     * @return false | simple_dom_node | array(simple_dom_node,)
     */
    public function evalHtml($html) 
    {
        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $domnode = $dom->getElementsByTagName('body');
        if($domnode->item(0) !== null)
        {
            $children = $domnode->item(0)->childNodes;
            if($children->length == 1)
            {
                $domnode = $children->item(0);
                $domnode = $this->html->importNode($domnode, true);
                $domnode = new simple_dom_node($this, $domnode);
                return $domnode;
            }
            else
            {
                $nodes = array();
                for($i=0; $i < $children->length; $i++)
                {
                    $domnode = $children->item($i);
                    $domnode = $this->html->importNode($domnode, true);
                    $nodes[] = new simple_dom_node($this, $domnode);
                }
                return $nodes;
            }
            return $domnode;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function toHtml() 
    {
        $html = $this->html->saveXML();
        simple_dom::fixUtf($html);
        // remove mooid's
        $html = preg_replace('/<(.*)?\s?mooid=["\'].+?["\']\s?(.*)?>/i', "<$1$2>", $html); 
        return $html;
    }

    public function __toString() 
    {
        return $this->toHtml();
    }

    /**
     * @param string $name
     * @return simple_dom_node |DOMDocument
     */
    public function __get($name) 
    {
        if($name == 'body') {
            $bodies = $this->html->getElementsByTagName('body');
            $node = new Dom\Node($this, $bodies->item(0));
            return $node;
        } else if($name == 'head') {
            $bodies = $this->html->getElementsByTagName('head');
            $node = new Dom\Node($this, $bodies->item(0));
            return $node;
        } else {
            return $this->$name;
        }
    }
    
    static public function fixUtf(&$html)
    {
        $html = str_replace('&#10;', "\n", $html);
        $html = str_replace(array('<![CDATA[', ']]>'), "", $html);
        $html = str_replace("\xC3\x82", '&nbsp;', $html);
        $html = str_replace("\xC3\xA2\xC2\x80", '&ndash;', $html);
        $html = str_replace("�", '', $html);
    }
}
?>
