<?php

/**
* process txt by line
* 这个类是专门用来被继承的，然后你重载 everyLine方法，
* 运行之，就好了
*/
class LineProcessor
{
    public $lineNo;
    public $state;
    private $lines;
    private $curNodeTag;
    private $domPath;

    function __construct()
    {
        $this->lineNo = 0;
        $this->domPath = array();
    }

    protected function everyLine($line)
    {
        return $line;
    }

    private function processLine($line)
    {
        if (preg_match('/\<(\w+?)(\s|>|\/>)/', $line, $matches)) {
            $tag = $matches[1];
            $this->curNodeTag = $tag;
            $this->domPath[] = $tag;
        }
        print_r($this->domPath);
        $r = $this->everyLine($line);
        if ($r === false) {
            return false;
        } elseif (is_string($r)) {
            $this->lastLine = $r;
        }
        if (preg_match('/.*\<\/(\w+)\>/', $line, $matches)) {
            $tag = $matches[1];
            echo "$tag end\n";
            if ($this->domPath[count($this->domPath)-1] == $tag) {
                array_pop($this->domPath);
            }
        }
    }

    public function processFile($fname)
    {
        echo "processing file: $fname\n";
        if (!file_exists($fname)) {
            throw new Exception("file not exists: $fname", 1);
        }
        $f = fopen($fname, 'r');
        while (false !== ($this->curLine = $line = fgets($f, 4096))) {
            $this->lineNo++;
            echo "$this->lineNo: $line";
            if (false === $this->processLine($line)) {
                fclose($f);
                return;
            }
        }
        if (!feof($f)) {
            throw new Exception("unexpected fgets() fail", 1);
        }
        fclose($f);
    }

    public function processDir($dirStr)
    {
        if (!file_exists($dirStr)) {
            throw new Exception("dir not exists: $dirStr", 1);
        }
        if (!is_dir($dirStr)) {
            throw new Exception("$dirStr is not a dir", 1);
        }
        $dir = opendir($dirStr);
        if (!$dir) {
            throw new Exception("error when open dir: $dirStr", 1);
        }
        while (false !== ($fname = readdir($dir))) {
            $fpath = $dirStr.DIRECTORY_SEPARATOR.$fname;
            // echo "$fname\n";
            if (in_array($fname, array('.', '..', '.git', '.svn', '.DS_Store'))) {
                // echo "skip $fpath\n";
                continue;
            }
            // echo "$fpath\n";
            if (is_dir($fpath)) {
                // echo "$fpath is dir\n";
                $this->processDir($fpath);
            } elseif (preg_match('/\.phtml$/', $fname) && !preg_match('/\.bak\./', $fname)) {
                // echo "$fpath is a file\n";
                $changes = $this->processFile($fpath);
                if ($changes) {
                    wecho("$fpath changed");
                }
            }
        }
    }

    private function wecho($str = '')
    {
        $str = str_replace('\\', '/', $str);
        echo iconv('utf-8', 'gbk', $str), PHP_EOL;
    }

    private function lead_spaces($line)
    {
        $num = strlen($line) - strlen(ltrim($line));
        return str_repeat(' ', $num);
    }
}
