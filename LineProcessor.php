<?php

/**
* process txt by line
* 这个类是专门用来被继承的，然后你重载 everyLine方法，
* 运行之，就好了
*/
class LineProcessor
{
    protected $lineNo;
    protected $curLine;
    protected $lastLine;
    protected $lines;
    protected $state;
    protected $curNodeTag;
    protected $domPath;
    protected $filePath;

    private $hasChanged;

    public function __construct()
    {
        $this->lineNo = 0;
        $this->domPath = array();
    }

    protected function everyLine($line)
    {
        return $line;
    }

    protected function line($i = 0, $str = null)
    {
        if ($i < 0) {
            throw new Exception("must be positive: $i", 1);
        }
        if ($str !== null) {
            // set
            if ($i > 0) {
                $this->lines[count($this->lines) - $i] = $str;
            }
        }

        // get
        if ($i == 0) {
            return $this->curLine;
        }
        return $this->lines[count($this->lines) - $i];
    }

    private function processLine($line)
    {
        if (preg_match('/\<(\w+?)(\s|>|\/>)/', $line, $matches)) {
            $tag = $matches[1];
            $this->curNodeTag = $tag;
            $this->domPath[] = $tag;
        }
        $r = $this->everyLine($line);
        if ($r === false) {
            return false;
        } elseif (is_string($r)) {
            $this->lines[] = $this->lastLine = $r;
            if (strcmp($r, $line) !== 0) {
                $this->hasChanged = true;
            }
        } else {
            $this->lines[] = $this->lastLine = $line;
        }
        if (preg_match('/.*\<\/(\w+)\>/', $line, $matches)) {
            $tag = $matches[1];
            if ($this->domPath[count($this->domPath)-1] == $tag) {
                array_pop($this->domPath);
            }
        }
    }

    public function processFile($fname)
    {
        // echo "processing file: $fname\n";
        if (!file_exists($fname)) {
            throw new Exception("file not exists: $fname", 1);
        }

        $this->hasChanged = false;
        $this->lineNo = 0;
        $this->lines = array();

        $f = fopen($fname, 'r');
        while (false !== ($this->curLine = $line = fgets($f, 4096))) {
            $this->lineNo++;
            // echo "$this->lineNo: $line";
            if (false === $this->processLine($line)) {
                fclose($f);
                return;
            }
        }
        if (!feof($f)) {
            throw new Exception("unexpected fgets() fail", 1);
        }
        fclose($f);

        if ($this->hasChanged) {
            echo "file has changed: $fname\n";
            $f = fopen($fname, 'w');
            foreach ($this->lines as $line) {
                $r = fwrite($f, $line);
                if ($r === false) {
                    fclose($f);
                    throw new Exception("file write error", 1);
                }
            }
            fclose($f);
        }
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
            if (in_array($fname, array('.', '..', '.git', '.svn', '.DS_Store'))) {
                continue;
            }
            // echo "$fpath\n";
            if (is_dir($fpath)) {
                // echo "$fpath is dir\n";
                $this->processDir($fpath);
            } elseif (preg_match('/\.phtml$/', $fname) && !preg_match('/\.bak\./', $fname)) {
                // echo "$fpath is a file\n";
                $this->filePath = $fpath;
                $changes = $this->processFile($fpath);
                if ($changes) {
                    echo("$fpath changed\n");
                }
            }
        }
    }

    private function lead_spaces($line)
    {
        $num = strlen($line) - strlen(ltrim($line));
        return str_repeat(' ', $num);
    }
}
