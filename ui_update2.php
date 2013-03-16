<?php

require 'LineProcessor.php';

/**
* ....
*/
class MyProcessor extends LineProcessor
{
    
    public function everyLine($line)
    {
        // echo $line;
    }
}

$pro = new MyProcessor();
$pro->processDir('/Users/xiaochi/Downloads/ZendSkeletonApplication-master');

