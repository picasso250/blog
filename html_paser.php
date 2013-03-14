<?php
$fname = 'a.html';

$parser = new HtmlParser();
$parser->parse($fname);
// print_r($parser->root());
print_node($parser->root(), 8);

function print_node(Node $node, $level, $space=0)
{
    for ($i=0; $i < $space; $i++) { 
        echo '  ';
    }
    echo "$node->tag";
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

    private function processLine($line, $lineno)
    {
        if (preg_match('/<(\w+)>/', $line, $matches) || preg_match('/<(\w+)\s+\w+.*>/', $line, $matches)) {
            $tagname = $matches[1];
            echo "$tagname tag starts\n";
            $curtag = $tagname;
            $node = new Node($tagname);
            $node->opening = 1;
            if ($this->cur->opening) {
                $this->cur->addChild($node);
                $this->cur = $node;
            } else {
                throw new Exception("cur node not open", 1);
            }
        }
        if (preg_match('/<\/(\w+)>/', $line, $matches)) {
            $tagname = $matches[1];
            echo "$tagname tag ends\n";
            if ($this->cur->tag == $tagname) {
                $this->cur->opening = 0;
                $this->cur = $this->cur->parent;
            }
        }
    }

    private tagStart()
    {

    }

    private tagEnd()
    {
        
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
            // echo "$lineno: $line";
            if (false === $this->processLine($line, $lineno))
                break;
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