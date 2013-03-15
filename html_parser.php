<?php
$fname = 'a.html';

$parser = new HtmlParser();
$parser->parse($fname);
print_node($parser->root(), 8);

function print_node(Node $node, $level, $space=0)
{
    for ($i=0; $i < $space; $i++) { 
        echo '  ';
    }
    echo "$node->tag";
    if ($node->attributes) {
        echo " { ";
        foreach ($node->attributes as $key => $value) {
            echo "$key=\"$value\" ";
        }
    }
    if ($level < 0) {
        echo '. Level end.', PHP_EOL;
        return;
    }
    if ($node->childNodes) {
        echo "\n";
        foreach ($node->childNodes as $key => $child) {
            print_node($child, $level-1, $space+1);
        }
    } else {
        echo "\n";
    }
}

/**
* processor of html
*/
class HtmlParser
{
    private $root; // root element

    public function __construct()
    {
        $this->root = new Node('Root');
        $this->root->opening = 1;
        $this->cur = $this->root;
    }

    private function processLine($line, $lineNo)
    {
        if ($lineNo > 32) {
            return false;
        }
        echo "$lineNo: $line";
        if (preg_match('/<(\w+)>/', $line, $matches) || preg_match('/<(\w+?)\s+(\w+.*)>/', $line, $matches)) {
            $tagname = $matches[1];
            $attrStr = isset($matches[2]) ? $matches[2] : null;
            $attrArr = $this->explodeSpace($attrStr);
            $this->_tagStart($tagname, $attrArr);
        }
        if (preg_match('/<(\w+?)(.*)\/>/', $line, $matches)) {
            $tagname = $matches[1];
            $attrStr = isset($matches[2]) ? $matches[2] : null;
            $this->_tagStart($tagname, $attrStr);
            $this->_tagEnd($tagname);
        }
        if (preg_match('/\<\w+.*\>(.*)\<\/\w+\>/', $line, $matches)) {
            $tagname = 'Text';
            $text = isset($matches[1]) ? $matches[1] : '';
            $this->_tagStart($tagname, $text);
            $this->_tagEnd($tagname);
        }
        if (preg_match('/\<!--/', $line)) {
            $tagname = 'Comment';
            $this->_tagStart($tagname);
        }
        if (preg_match('/-->/', $line)) {
            $tagname = 'Comment';
            $this->_tagEnd($tagname);
        }
        if (preg_match('/<\/(\w+)>/', $line, $matches)) {
            $tagname = $matches[1];
            $this->_tagEnd($tagname);
        }
    }

    private function _tagStart($tagname, $attrArr = null)
    {
        $node = new Node($tagname);
        if (is_string($attrArr)) {
            $node->value = $attrArr;
        } else {
            $node->attributes = $this->kvlize($attrArr);
        }
        print_r($node);
        $node->opening = 1;
        if ($this->cur->opening) {
            $this->cur->addChild($node);
            $this->cur = $node;
        } else {
            throw new Exception("cur node not open", 1);
        }
    }

    private function _tagEnd($tagname)
    {
        echo "$tagname tag ends\n";
        if ($this->cur->tag == $tagname) {
            $this->cur->opening = 0;
            $this->cur = $this->cur->parent;
        }
    }

    private function kvlize($rawAttrArr)
    {
        $ret = array();
        if (is_array($rawAttrArr) && $rawAttrArr) {
            foreach ($rawAttrArr as $str) {
                $arr = explode('=', trim($str));
                $key = $arr[0];
                if ($key == '/') {
                    continue;
                }
                $value = isset($arr[1]) ? trim(trim($arr[1], '/'), '"') : null;
                $ret[$key] = $value;
            }
        }
        return $ret;
    }

    private function explodeSpace($str)
    {
        $str = trim($str);
        if (!$str) {
            return array();
        }
        $raw_arr = explode(' ', $str);
        if ($raw_arr) {
            $ret = array();
            foreach ($raw_arr as $raw_str) {
                $str = trim($raw_str);
                if ($str) {
                    $ret[] = $str;
                }
            }
            return $ret;
        } else {
            return array();
        }
    }
    
    public function parse($fname)
    {
        if (!file_exists($fname)) {
            throw new Exception("file not exists: $fname", 1);
        }
        $f = fopen($fname, 'r');
        $lineno = 0;
        while (false !== ($line = fgets($f, 4096))) {
            $lineno++;
            if (false === $this->processLine($line, $lineno)) {
                fclose($f);
                return;
            }
        }
        if (!feof($f)) {
            throw new Exception("unexpected fgets() fail", 1);
        }
        fclose($f);
    }

    public function root()
    {
        return $this->root;
    }
}


class Node {
    private static $nextId;
    public $id;
    public $tag;
    public $childNodes;
    public $parent;
    public $attributes;

    public function __construct($tag)
    {
        if (!self::$nextId) {
            self::$nextId = 1;
        }
        $this->id = self::$nextId++;
        $this->tag = $tag;
    }

    public function addChild(Node $child)
    {
        $child->parent = $this;
        if (!$this->childNodes) {
            $this->childNodes = array($child);
        } else {
            $this->childNodes[] = $child;
         }
    }
}