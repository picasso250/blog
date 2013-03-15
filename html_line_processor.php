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

    function __construct()
    {
        $this->lineNo = 0;
    }

    protected function everyLine($line)
    {
        return $line;
    }

    private processLine($line)
    {
        $r = $this->everyLine($line);
        if ($r === false) {
            return false;
        } elseif (is_string($r)) {
            $this->lastLine = $r;
        }
    }

    public processFile($fname)
    {
        if (!file_exists($fname)) {
            throw new Exception("file not exists: $fname", 1);
        }
        $f = fopen($fname, 'r');
        while (false !== ($this->curLine = $line = fgets($f, 4096))) {
            $this->lineNo++;
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
        while (false !== ($fname = readdir($dir))) {
            $fpath = $dirStr.DIRECTORY_SEPARATOR.$fname;
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
}








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

        if (preg_match('/<div class="conditions" role="advancedSearchConditions">/', $line)) {
            // wecho("$url line: $i 高级搜索条件 需隐藏");
        }

        // 操作按钮
        if (preg_match('/<td class="opt">/', $line)) {
            // wecho("$url line: $i 操作按钮");
            $last_state = 'td_opt';
            $acnt = 0;
            $with_php = 0;
            $i_td = $i;
            $last_line = $line;
        }

        if (preg_match('/详情/', $line) 
            && $last_state == 'td_opt' 
            && preg_match('/<td class="opt">/', $last_line)
            && !preg_match('/demand-agreement/', $line)
            && preg_match('/loadModule\(this/', $line)
            && !preg_match('/url\(/', $line)
            ) {
            $last_state = 'opt_2a';
            $lead_spaces = lead_spaces($line);
            $newlines[] = $lead_spaces.'<span class="opt-container" role="moreOptMenu">';
            $newlines[] = $lead_spaces.'    <span class="moreopt" role="wrap">';
            $newlines[] = $lead_spaces.'         <span class="moreopt-inner">';
            $line = preg_replace('/href="/', 'class="tit" role="tit" href="', $line);
            $line = preg_replace('/>详情</', '><em>详情<i></i></em><', $line);
            $changes[] = $i;
        }
        if ($last_state == 'opt_2a' && preg_match('/审核/', $line)) {
            $newlines[] = $lead_spaces.'             <div class="sub" role="sub">';
            $changes[] = $i;
            $line = preg_replace('/>审核</', '><span><em>审核</em></span><', $line);
            $changes[] = $i;
        }

        if (preg_match('/<a href="###">删除|<a href="###">查看|<a href="###">下载/', $line)
            || (preg_match('/a.+下载/', $line) && preg_match('/a.+查看/', $newlines[count($newlines)-1]))
        ) {
            $last_state = null;
        }
        if (preg_match('/<span class=".*role="moreOptMenu">/', $line)) {
            $last_state = null;
        }
        if ($last_state == 'td_opt' && preg_match('/<a.+?>/', $line)) {
            $acnt++;
        }
        if ($last_state == 'td_opt' && preg_match('/^\s*<\?php/', $line)) {
            $with_php = 1;
        }

        // 审核
        if ($last_state == 'td_opt' && preg_match('/dictionary.*详情/', $line) && preg_match('/td.*opt/', $newlines[count($newlines)-1])) {
            $last_state = 'opt_view';
            $lead_spaces = lead_spaces($line);
            $newlines[] = $lead_spaces.'<span class="opt-container" role="moreOptMenu">';
            $newlines[] = $lead_spaces.'    <span class="moreopt" role="wrap">';
            $newlines[] = $lead_spaces.'         <span class="moreopt-inner">';
            // wecho($line);
            $line = preg_replace('/href="/', 'class="tit" role="tit" href="', $line);
            $line = preg_replace('/>详情</', '><em>详情<i></i></em><', $line);
            // wecho($l);
            $changes[] = $i;
            $last_line = $line;
        }
        if ($last_state == 'opt_view' 
            && preg_match('/^\s*<\?php/', $line) 
            && preg_match('/a.+详情/', $last_line)
        ) {
            $newlines[] = $lead_spaces.'             <div class="sub" role="sub">';
            $changes[] = $i;
        }
        if ($last_state == 'opt_view' && preg_match('/通过|拒绝/', $line)) {
            $line = preg_replace('/(通过|拒绝)/', '<span class="no"><span><em>$1</em></span></span>', $line);
        }
        if ($last_state == 'opt_view' && preg_match('/审核/', $line)) {
            $line = preg_replace('/>审核</', '><span><em>审核</em></span><', $line);
            $changes[] = $i;
            $last_line = $line;
        }
        if (($last_state == 'opt_view' || $last_state == 'opt_2a')
            && preg_match('/<\/td>/', $line)) {
            // addd
            $newlines[] = $lead_spaces.'            </div>';
            $newlines[] = $lead_spaces.'        </span>';
            $newlines[] = $lead_spaces.'    </span>';
            $newlines[] = $lead_spaces.'</span>';
            $changes[] = $i;
            $last_state = null;
        }

        // 之前的
        if (preg_match('/class="tit" role="tit"/', $line) && preg_match('/span>/', $line)) {
            $line = preg_replace('/<span><em>/', '<em>', $line);
            $line = preg_replace('/<\/em><\/span>/', '</em>', $line);
            $changes[] = $i;
        }
        if (preg_match('/<div class="sub" role="sub">/', $line)) {
            $sub_mode = 1; // enter sub mode
        }
        if (isset($sub_mode) && $sub_mode && !preg_match('/span.+em/', $line) && preg_match('/<em>.+<i><\/i><\/em>/', $line) && !preg_match('/span>/', $line)) {
            $line = preg_replace('/<em>(.+)<i><\/i><\/em>/', '<span><em>$1<i></i></em></span>', $line);
            $changes[] = $i;
            $sub_mode = 0; // leave sub mode
        }

        if ($last_state == 'td_opt' && preg_match('/<\/td>/', $line)) {
            $last_state = null;
            $last_line = $newlines[count($newlines)-1];
            if ($acnt == 1 && !preg_match('/ class="btn btn-grid-opt".*<em/', $last_line)) {
                wecho("$url line: $i_td 操作按钮 仅 1个");
                if (preg_match('/<a.*onclick="loadModule\(this\)/', $last_line)) {
                    // wecho($last_line);
                    $tl = preg_replace('/href="/', 'class="btn btn-grid-opt" href="', $last_line);
                    $tl = preg_replace('/">(.+)</', '"><em>$1</em><', $tl);
                    $newlines[count($newlines)-1] = $tl;
                    $changes[] = count($newlines);
                    // wecho($l);
                }
            }
            if ($acnt > 1) { // 只有一个的保持原样？？
                wecho("$url line: $i_td 操作按钮");
            }
            if ($acnt > 2 && $with_php) {
                wecho("$url line: $i_td 操作按钮 with php");
            }
        }

        if (preg_match('/input.*关键字/', $line)) {
            $small_search_btn_mode = 1;
        }
        if (isset($small_search_btn_mode) && $small_search_btn_mode && preg_match('/label.*button.*搜索/', $line)) {
            wecho("$url line: $i 小搜索按钮");
            $line = preg_replace('/<label.*label>/', '<button hidefocus="" class="btn btn-sh btn-sh-fixed" type="submit"><em>搜索</em></button>', $line);
            $changes[] = $i;
        }
        if (preg_match('/<\/div>/', $line)) {
            $small_search_btn_mode = 0;
        }

        // datepicker
        if (preg_match('/<input.*class=".*datepicker.*".*role="datePicker".*>/', $line) 
            && !preg_match('/props="/', $line) 
            && preg_match('/id="\d/', $line)
        ) {
            wecho("$url line: $i datePicker");
            wecho($line);
            if (preg_match('/id="dp1/', $line)) {
                $props = 'props="maxDate:\'#F{$dp.$D(\\\'dp2\\\')}\'';
            } elseif (preg_match('/id="dp2/', $line)) {
                $props = 'props="minDate:\'#F{$dp.$D(\\\'dp1\\\')}\'';
            } else {
                $props = 'role="datePicker';
            }
            $l = preg_replace('/role="datePicker/', $props, $line);
            wecho($l);
            // $changes[] = $i;
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



class Line
