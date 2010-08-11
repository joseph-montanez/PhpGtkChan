<?php
/**
 * @package Node 
 */
namespace Gorilla3D\Dom;

class Node {
    function __construct(&$owner, &$node) 
    {
        /*if($this->node instanceof DOMText) {
            throw new Exception('Element you want create is not a DomNode its a DOMText');
        } else {*/
            $this->owner = $owner;
            $this->node = $node;
            $this->mooid = uniqid();
            $this->setProperty('mooid', $this->mooid);
        /*}*/
    }

    public function toCssSelector($value) 
    {
        if(!strstr($value, '/')) {
            $value = self::transform($value);
        }
        return $value;
    }
    
    public function transform($path)
    {
        $path = (string) $path;
        if (strstr($path, ',')) {
            $paths       = explode(',', $path);
            $expressions = array();
            foreach ($paths as $path) {
                $xpath = self::transform(trim($path));
                if (is_string($xpath)) {
                    $expressions[] = $xpath;
                } elseif (is_array($xpath)) {
                    $expressions = array_merge($expressions, $xpath);
                }
            }
            return $expressions;
        }

        $paths    = array('//');
        $segments = preg_split('/\s+/', $path);
        foreach ($segments as $key => $segment) {
            $pathSegment = self::_tokenize($segment);
            if (0 == $key) {
                if (0 === strpos($pathSegment, '[contains(@class')) {
                    $paths[0] .= '*' . $pathSegment;
                } else {
                    $paths[0] .= $pathSegment;
                }
                continue;
            }
            if (0 === strpos($pathSegment, '[contains(@class')) {
                foreach ($paths as $key => $xpath) {
                    $paths[$key] .= '//*' . $pathSegment;
                    $paths[]      = $xpath . $pathSegment;
                }
            } else {
                foreach ($paths as $key => $xpath) {
                    $paths[$key] .= '//' . $pathSegment;
                }
            }
        }

        if (1 == count($paths)) {
            return $paths[0];
        }
        return implode(' | ', $paths);
    }
    
    protected static function _tokenize($expression)
    {
        // Child selectors
        $expression = str_replace('>', '/', $expression);

        // IDs
        $expression = preg_replace('|#([a-z][a-z0-9_-]*)|i', '[@id=\'$1\']', $expression);
        $expression = preg_replace('|(?<![a-z0-9_-])(\[@id=)|i', '*$1', $expression);

        // arbitrary attribute strict equality
        if (preg_match('|([a-z]+)\[([a-z0-9_-]+)=[\'"]([^\'"]+)[\'"]\]|i', $expression)) {
            $expression = preg_replace_callback(
                '|([a-z]+)\[([a-z0-9_-]+)=[\'"]([^\'"]+)[\'"]\]|i',
                create_function(
                    '$matches',
                    'return $matches[1] . "[@" . strtolower($matches[2]) . "=\'" . $matches[3] . "\']";'
                ),
                $expression
            );
        }

        // arbitrary attribute contains full word
        if (preg_match('|([a-z]+)\[([a-z0-9_-]+)~=[\'"]([^\'"]+)[\'"]\]|i', $expression)) {
            $expression = preg_replace_callback(
                '|([a-z]+)\[([a-z0-9_-]+)~=[\'"]([^\'"]+)[\'"]\]|i',
                create_function(
                    '$matches',
                    'return $matches[1] . "[contains(@" . strtolower($matches[2]) . ", \' $matches[3] \')]";'
                ),
                $expression
            );
        }

        // arbitrary attribute contains specified content
        if (preg_match('|([a-z]+)\[([a-z0-9_-]+)\*=[\'"]([^\'"]+)[\'"]\]|i', $expression)) {
            $expression = preg_replace_callback(
                '|([a-z]+)\[([a-z0-9_-]+)\*=[\'"]([^\'"]+)[\'"]\]|i',
                create_function(
                    '$matches',
                    'return $matches[1] . "[contains(@" . strtolower($matches[2]) . ", \'" . $matches[3] . "\')]";'
                ),
                $expression
            );
        }

        // Classes
        $expression = preg_replace('|\.([a-z][a-z0-9_-]*)|i', "[contains(@class, ' \$1 ')]", $expression);

        return $expression;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return array
     * @see function getElementByAttribute
     */
    private function getElementsByAttribute($attribute, $value) 
    {
        return $this->getElements('//*[@' . $attribute . '="' . $value . '"]');
    }

    /**
     * @param string $attribute
     * @param string $value
     * @return Node
     * @see function getElementsByAttribute
     */
    private function getElementByAttribute($attribute, $value) 
    {
        $elements = $this->getElementsByAttribute($attribute, $value);
        if(!empty($elements)) {
            return $elements[0];
        } else {
            return false;
        }
    }

    /**
     * @param string $id
     * @return Node
     */
    public function getElementById($id) 
    {
        return $this->getElementByAttribute('id', $id);
    }


    /**
     * @param string $attribute
     * @return Node
     */
    public function erase($attribute) 
    {
        $this->node->removeAttribute($attribute);
        return $this;
    }

    /**
     * getElements
     *
     * Using XPath:
     * <code>
     * <?php
     * // Get all <div> tags in the body
     * $elements = $document->body->getElements('//div');
     * ?>
     * </code>
     * Using Css Selectors:
     * <code>
     * <?php
     * // Get all <div> tags in the body
     * $elements = $document->body->getElements('div');
     * ?>
     * </code>
     * @param string $selector The XPath Query
     * @return array
     */
    public function getElements($selector) 
    {
        $selector = $this->toCssSelector($selector);
        $query = '';
        $query .= '//' . $this->node->tagName . '[@mooid="' . $this->mooid . '"]';
        $query .= $selector;
        $xpath = new \DOMXPath($this->owner->html);
        $rows = @$xpath->query($query);
        $nodes = array();
        for($i = 0; $i < $rows->length; $i++) {
            $row = $rows->item($i);
            $nodes []= new Node($this->owner, $row);
        }
        return $nodes;
    }

    /**
     * @param string $selector
     * @return Node
     */
    public function getElement($selector) 
    {
        $elements = $this->getElements($selector);
        $element = $elements[0];
        if(!$element) {
            $htmls = $this->owner->html->getElementsByTagName('html');
            $node = new Node($this->owner, $htmls->item(0));
            $elements = $node->getElements($q);
            $element = $elements[0];
        }
        if(!$element) {
            throw new Exception('Element you want is not found');
        }
        return $element;
    }

    public function set($name, $value) 
    {
        if($name == 'text') 
        {
            $newtext = new DOMText($value);
            $childs = $this->node->childNodes;
            if($childs) {
                $nth = 0;
                $text = $childs->item($nth);
                while(!$childs->item($nth) instanceof DOMText) {
                    $nth++;
                    $text = $childs->item($nth);
                    if($text === null) {
                        $text = $childs->item($nth - 1);
                        break;
                    }
                }

                if($text === null) {
                    $this->node->appendChild($newtext);
                } else {
                    $this->node->replaceChild($newtext, $text);
                }
            } else {
                $this->node->appendChild($newtext);
            }
        } 
        else if($name == 'html') 
        {
            $this->clear();
            if(is_string($value) and !strstr($value, '<') and !strstr($value, '>'))
            {
                return $this->set('text', $value);
            } 
            else 
            {
                $node = $this->owner->evalHtml($value);
                if(is_array($node))
                {
                    $nodes = $node;
                    foreach($nodes as $node) 
                    {
                        $node->inject($this);
                    }
                } 
                else 
                {
                    $node->inject($this);
                }
            }
        } 
        else 
        {
            $this->setProperty($name, $value);
        }
        return $this;
    }

    public function get($name) 
    {
        if($name == 'html') {
            $html = '';
            if($this->node->childNodes) {
                foreach($this->node->childNodes as $node) {
                    $html .= $node->ownerDocument->saveXML($node);
                }
            } else {
            }
            Gorilla3D\Dom::fixUtf($html);
            return $html;
        } else if($name == 'text') {
            return $this->node->textContent;
        } else if($name == 'tag') {
            return $this->node->tagName;
        } else {
            return $this->getProperty($name);
        }
    }

    public function getParent() 
    {
        if($this->node->parentNode) {
            $parent = new Node($this->owner, $this->node->parentNode);
            $this->parent = $parent;
            return $parent;
        }
        return false;

    }

    public function copy($contents = true, $keepid = false) 
    {
        $dom = new Gorilla3D\Dom(trim($this->toHtml()));
        $parnode = $dom->html->getElementsByTagName('body')->item(0);
        $chlnode = $parnode->childNodes->item(0);
        $i= 0;
        while($chlnode instanceof DOMText) {
            $i++;
            $chlnode = $parnode->childNodes->item($i);
        }
        $domnode = new Node($dom, $chlnode);
        if(!$keepid) {
            $domnode->set('id', '');
        }
        if(!$contents) {
            $domnode->clear();
        }
        $domnode->mooid = uniqid();
        $domnode->setProperty('mooid', $domnode->mooid);
        return new Node($domnode->owner, $domnode->node);

    }

    public function setProperty($prop, $value) 
    {
        if(!$this->node) {
            throw new Exception('Element you want is not found');
        }
        if($this->node instanceof DOMText or $this->node instanceof DOMComment) {
            return $this;
            //throw new Exception('Element you want not a DomNode its a DOMText');
        }
        $this->node->setAttribute($prop, $value);
        return $this;
    }

    public function getProperty($prop) 
    {
        return $this->node->getAttribute($prop);
    }

    public function getDocument() 
    {
        return $this->node->ownerDocument;
    }

    public function toHtml() 
    {
        $html = $this->getDocument()->saveXML($this->node);
        Gorilla3D\Dom::fixUtf($html);
        /* 
         * Remove moo ids
         */
        $html = preg_replace('/<(.*)?\s?mooid=["\'].+?["\']\s?(.*)?>/i', "<$1$2>", $html); 
        return $html;
    }

    public function inject($node, $location = 'bottom') 
    {
        if($this->node->ownerDocument !== $node->node->ownerDocument) {
            if($node->node === null) {
                throw new Exception('Element you want to inject into is null');
            };
            $this->node = $node->node->ownerDocument->importNode($this->node, true);
        }
        $this->parent = $node;
        if($location == 'bottom') {
            $this->node = $node->node->appendChild($this->node);
        } else if($location == 'top') {
            $first = $node->node->firstChild;
            if($first) {
                $this->node = $node->node->insertBefore($this->node, $first);
            } else {
                $this->node = $this->inject($this->node, 'bottom');
            }
        } else if($location == 'after') {
            $sibling = $node->node->nextSibling;
            if($sibling) {
                $this->node = $node->node->parentNode->insertBefore($this->node, $sibling);
            } else {
                $this->node = $this->inject($node->getParent());
            }
        }
        return $this;
    }

    /**
     * @param string $selector
     * @return Node
     */
    public function getFirst($selector='') 
    {
        $first = $this->node->childNodes->item(0);
        if($first) {
            return new Node($this->owner, $first);
        } else {
            return false;
        }
    }

    /**
     * @param string $selector
     * @return Node
     */
    public function getLast() 
    {
        $last = $this->node->childNodes->item($this->node->childNodes->length - 1);
        if($last) {
            return new Node($this->owner, $last);
        } else {
            return false;
        }
    }
    
    public function getNext() 
    {
        $last = $this->node->nextSibling;
        if($last) {
            return new Node($this->owner, $last);
        } else {
            return false;
        }
    } 
    
    public function getPrevious() 
    {
        $last = $this->node->previousSibling;
        if($last) {
            return new Node($this->owner, $last);
        } else {
            return false;
        }
    } 

    public function dispose() 
    {
        return $this->node->parentNode->removeChild($this->node);
    }

    public function clear() 
    {
        if($this->node->hasChildNodes()) {
            while($this->node->childNodes->length) {
                $this->node->removeChild($this->node->firstChild);
            }
        }
        return $this;
    }
    
    public function __toString()
    {
        return $this->toHtml();
    }
}
?>
