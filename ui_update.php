<?php

define('TEST_URL_NEEDED', 0);

$fname = 'list.phtml';
up_ui_dir('.');



function up_ui_dir($dir_str)
{
    // wecho("working on dir: $dir_str");
    if (!file_exists($dir_str)) {
        throw new Exception("dir not exists: $dir_str", 1);
    }
    if (!is_dir($dir_str)) {
        throw new Exception("$dir_str is not a dir", 1);
    }
    $dir = opendir($dir_str);
    while (false !== ($fname = readdir($dir))) {
        $fpath = $dir_str.DIRECTORY_SEPARATOR.$fname;
        // wecho("processing $fpath");
        if ($fname === '.' || $fname == '..') {
            continue;
        }
        if (is_dir($fpath)) {
            up_ui_dir($fpath);
        } elseif (preg_match('/\.phtml$/', $fname) && !preg_match('/\.bak\./', $fname)) {
            $url = path2url($fpath);
            if (TEST_URL_NEEDED && !is_http_200($url)) {
                // wecho("$url http code not 200, skip");
                continue;
            }
            $changes = up_ui_f($fpath);
            if ($changes) {
                wecho("$fpath changed");
            }
        }
    }
}

function up_ui_f($fname)
{
    if (!file_exists($fname)) {
        throw new Exception("file not exists: $fname", 1);
    }
    $url = path2url($fname);

    // skip 桂鹏的
    if (preg_match('/index.field/', $fname)) {
        return;
    }

    $code = file_get_contents($fname);
    $lines = explode("\n", $code);

    $root = new Node('Root');
    $root->opening = 1;
    $cur = $root;

    $newlines = array();
    $i = 0;
    $changes = array();
    $last_state = '';
    $last_line = '';
    foreach ($lines as $line) {
        $i++;

        if (preg_match('/<(\w+)>/', $line, $matches) || preg_match('/<(\w+)\s+\w+.*>/', $line, $matches)) {
            $tagname = $matches[1];
            $node = new Node($tagname);
            if (preg_match('/<\w+\s+(.*?)>/', $line, $matches)) {
                $attrs_str = $matches[1];
                $raw_attr_arr = explode_space($attrs_str);
                $attr_arr = array();
                foreach ($raw_attr_arr as $attr_str) {
                    $kva = explode('=', $attr_str);
                    $key = trim($kva[0]);
                    if ($key === '/') {
                        continue;
                    }
                    if (isset($kva[1])) {
                        $value = trim(trim($kva[1]), '"');
                    } else {
                        $value = null;
                    }
                    
                    if ($key == 'class') {
                        $value = explode_space($value);
                    }
                    $attr_arr[$key] = $value;
                }
                $node->attrbutes = $attr_arr;
            }
            $node->opening = 1;
            if ($cur->opening) {
                $cur->addChild($node);
                $cur = $node;
            } else {
                throw new Exception("cur node not open", 1);
            }
        }
        if (preg_match('/<\/(\w+)>/', $line, $matches)) {
            $tagname = $matches[1];
            if ($cur->tag == $tagname) {
                $cur->opening = 0;
                $cur = $cur->parent;
            }
        }

        // 新增按钮
        if (preg_match('/btn btn-link.+><em>新增/', $line)) {
            $line = preg_replace('/btn btn-link/', 'btn btn-link btn-link-new', $line);
            $line = preg_replace('/><em>/', '><em><i></i>', $line);
            $changes[] = $i;
        }

        // 高级搜索按钮
        if (preg_match('/btn btn-link.+><em>高级搜索/', $line)) {
            $line = preg_replace('/btn btn-link/', 'btn btn-link btn-link-search', $line);
            $line = preg_replace('/><em>/', '><em><i></i>', $line);
            $changes[] = $i;
        }
        if (preg_match('/btn btn-link btn-link-search.+高级搜索/', $line) && !preg_match('/display:none/', $line)) {
            $line = preg_replace('/class/', 'style="display:none" class', $line);
            $changes[] = $i;
            // wecho("$url line: $i 高级搜索按钮需要隐藏");
            // wecho($l);
        }

        // 树形菜单，需人工参与解决
        if (preg_match('/tree-menu-title/', $last_line)) {
            if (!preg_match('/span>/', $line)) {
                wecho("$url line: ".($i-1)." need to add span");
            }
        }

        // 表头的搜索
        if (preg_match('/<tr class="search">/', $line)) {
            $line = preg_replace('/<tr.+?>/', '<tr class="search" role="searchRow">', $line);
            $last_state = 'tr_search';
            $changes[] = $i;
        }
        if (preg_match('/<label.+<button.+>搜索<\/button><\/label>/', $line)) {
            // wecho("$url line: $i need to view about searchBtn");
            if (preg_match('/hidefocus/', $line)) {
                $right_line = '<button role="searchBtn" class="btn btn-sh btn-sh-fixed" hidefocus type="submit"><em>搜索</em></button>';
            } else {
                $right_line = '<button role="searchBtn" class="btn btn-sh btn-sh-fixed" type="submit"><em>搜索</em></button>';
            }
            if ($last_state == 'tr_search') {
                $line = preg_replace('/<label.*<\/label>/', $right_line, $line);
                $changes[] = $i;
            }
            $last_state = 'search_btn';
        }

        // input width
        if (preg_match('/input type="text" class="in-t" .+ style="width:(12|20|26|13)px;"/', $line)) {
            // wecho("$url line: $i need to view about width");
        }

        // 高级搜索面板里的搜索和关闭按钮
        if (preg_match('/<label class="btn btn-sh"><button.*type="submit">.*搜索.+button>.+label>/', $line)) {
            $last_line = $line;
        }
        if (preg_match('/role="advancedSearchClose">/', $line) 
            && isset($last_line) && preg_match('/label/', $last_line)
            && $cur->parent->tag === 'div') { // && $cur->parent->attributes && in_array('ft', $cur->parent->attributes['class'])) {
            wecho("$url line: $i adv-btn-close");

            $space_num = strlen($last_line) - strlen(ltrim($last_line));
            $space_str = '';
            for ($j=0; $j < $space_num; $j++) { 
                $space_str .= ' ';
            }

            array_pop($newlines);
            $last_line = $space_str.'<button type="submit" class="btn adv-btn-sh btn-sh-fixed"><em>搜索</em></button>';
            $line         = $space_str.'<a href="javascript:;" role="advancedSearchClose" class="adv-btn-close">关闭</a>';
            $newlines[] = $last_line;
            $changes[] = $i;
        }

        if (preg_match('/input.*关键字/', $line)) {
            $small_search_btn_mode = 1;
        }
        if (isset($small_search_btn_mode) 
            && $small_search_btn_mode 
            && preg_match('/label.*button.*搜索/', $line)
            && !preg_match('/role.+searchBtn/', $line)) {
            wecho("$url line: $i 小搜索按钮");
            $line = preg_replace('/<label.*label>/', '<button hidefocus="" role="searchBtn" class="btn btn-sh btn-sh-fixed" type="submit"><em>搜索</em></button>', $line);
            $changes[] = $i;
        }
        if (preg_match('/<\/div>/', $line)) {
            $small_search_btn_mode = 0;
        }

        $newlines[] = $line;
    }
    if ($changes) {
        $new_code = implode("\n", $newlines);
        $r = file_put_contents($fname, $new_code);
        if (false === $r) {
            throw new Exception("can not write to file: $fname", 1);
        }
    }
    return $changes;
}

function wecho($str = '')
{
    $str = str_replace('\\', '/', $str);
    echo iconv('utf-8', 'gbk', $str), PHP_EOL;
}

function explode_space($str)
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

function path2url($path)
{
    $arr = explode('.', $path);
    $raw_url = $arr[1];
    $raw_url = trim($raw_url, '\\');
    $arr = explode('\\', $raw_url);
    $url = 'http://fangyun.me/'.implode('/', $arr);
    return $url;
}

function lead_spaces($line)
{
    $num = strlen($line) - strlen(ltrim($line));
    return str_repeat(' ', $num);
}

function is_http_200($url)
{
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_COOKIE => 'PHPSESSID=ga6bvvm92ehrqsn98aqv7cant6',
        CURLOPT_RETURNTRANSFER => true,
    ));
    curl_exec($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    if (is_array($info) && $info['http_code'] == 200) {
        return true;
    } else {
        return false;
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
