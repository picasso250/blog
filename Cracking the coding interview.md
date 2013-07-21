<?php

// 1.1

// s1
function is_unique($str)
{
    $map = array();
    $n = strlen($str);
    for ($i=0; $i < $n; $i++) { 
        $c = $str[$i];
        if (isset($map[$c]) && $map[$c]) {
            return false;
        }
        $map[$c] = true;
    }
    return true;
}

// s2
function is_unique($str)
{
    $bitmap = new Bitmap();
    $n = strlen($str);
    for ($i=0; $i < $n; $i++) {
        $ord = ord($str[$i]);
        if ($bitmap->isSetBit($ord)) {
            return false;
        }
        $bitmap->setBit($ord);
    }
    return true;
}
class Bitmap
{
    private $map;
    private $segLength;

    public function __construct()
    {
        $this->map = array(0, 0, 0, 0, 0, 0, 0, 0);
        $this->segLength = PHP_INT_SIZE * 8;
    }
    public function isSetBit($pos)
    {
        $offset = $this->getOffset($pos);
        return $this->map[$offset] & $this->getMask($pos);
    }
    public function setBit($pos)
    {
        $offset = $this->getOffset($pos);
        $this->map[$offset] |= $this->getMask($pos);
    }
    private function getOffset($pos)
    {
        return floor($pos / $this->segLength);
    }
    private function getMask($pos)
    {
        return 1 << ($pos % $this->segLength);
    }
}

// 1.2
function reverse(&$str)
{
    $start = 0;
    $end = strlen($str) - 1;
    while ($start < $end) {
        list($str[$start], $str[$end]) = array($str[$end], $str[$start]);
        $start++;
        $end--;
    }
}

// 1.5
function replace(&$str)
{
    $arr = explode(' ', $str);
    $str = implode('%20', $arr);
}