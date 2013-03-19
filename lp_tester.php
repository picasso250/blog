<?php

require 'LineProcessor.php';

/**
* ....
*/
class MyProcessor extends LineProcessor
{
    
    protected function everyLine($line)
    {
        // echo $line;
        $url = path2url($this->filePath);

        $changes = array();

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
        }

        // 树形菜单，需人工参与解决
        if (preg_match('/tree\-menu\-title/', $this->lastLine)) {
                wecho("$url line: ".($i-1)." need to add span");
            if (!preg_match('/span/', $line)) {
            }
        }

        // 表头的搜索
        if (preg_match('/<tr class="search">/', $line)) {
            $line = preg_replace('/<tr.+?>/', '<tr class="search" role="searchRow">', $line);
            $this->state = 'tr_search';
            $changes[] = $i;
        }
        if (preg_match('/<label.+<button.+>搜索<\/button><\/label>/', $line)) {
            // wecho("$url line: $i need to view about searchBtn");
            if (preg_match('/hidefocus/', $line)) {
                $right_line = '<button role="searchBtn" class="btn btn-sh btn-sh-fixed" hidefocus type="submit"><em>搜索</em></button>';
            } else {
                $right_line = '<button role="searchBtn" class="btn btn-sh btn-sh-fixed" type="submit"><em>搜索</em></button>';
            }
            if ($this->state == 'tr_search') {
                $line = preg_replace('/<label.*<\/label>/', $right_line, $line);
                $changes[] = $i;
            }
            $this->state = 'search_btn';
        }

        // 高级搜索面板里的搜索和关闭按钮
        if (preg_match('/<label class="btn btn-sh"><button.*type="submit">.*搜索.+button>.+label>/', $line)) {
            $this->lastLine = $line;
        }
        if (preg_match('/role="advancedSearchClose">/', $line) 
            && isset($this->lastLine) && preg_match('/label/', $this->lastLine)
            && $cur->parent->tag === 'div') { // && $cur->parent->attributes && in_array('ft', $cur->parent->attributes['class'])) {
            wecho("$url line: $i adv-btn-close");

            $space_num = strlen($this->lastLine) - strlen(ltrim($this->lastLine));
            $space_str = '';
            for ($j=0; $j < $space_num; $j++) { 
                $space_str .= ' ';
            }

            array_pop($newlines);
            $this->lastLine = $space_str.'<button type="submit" class="btn adv-btn-sh btn-sh-fixed"><em>搜索</em></button>';
            $line         = $space_str.'<a href="javascript:;" role="advancedSearchClose" class="adv-btn-close">关闭</a>';
            $newlines[] = $this->lastLine;
            $changes[] = $i;
        }

        if (preg_match('/<div class="conditions" role="advancedSearchConditions">/', $line)) {
            // wecho("$url line: $i 高级搜索条件 需隐藏");
        }

        // 操作按钮
        if (preg_match('/<td class="opt">/', $line)) {
            // wecho("$url line: $i 操作按钮");
            $this->state = 'td_opt';
            $this->acnt = 0;
            $this->with_php = 0;
            $this->i_td = $this->lineNo;
            $this->lastLine = $line;
        }

        if (preg_match('/<span class=".*role="moreOptMenu">/', $line)) {
            $this->state = null;
        }
        if ($this->state == 'td_opt' && preg_match('/<a.+?>/', $line)) {
            $this->acnt++;
        }
        if ($this->state == 'td_opt' && preg_match('/^\s*<\?php/', $line)) {
            $this->with_php = 1;
        }
        if ($this->state == 'td_opt' && preg_match('/class.*tit/', $line)) {
            $this->state = null;
        }
        if ($this->state == 'td_opt' && preg_match('/<\/td>/', $line)) {
            $this->state = null;
            if ($this->acnt == 1 && !preg_match('/ class="btn btn-grid-opt".*<em/', $this->lastLine)) {
                // wecho("$url line: $this->i_td 操作按钮 仅 1个");
                // wecho($this->lastLine);
                echo "$line";;
                // var_dump($this->lastLine);
                // var_dump($this->curLine);
                $s = trim($this->lastLine);
                if (empty($this->lastLine)) {
                    wecho('empty !!!!!!!!!!!!!!!!!!');
                }
                if (!trim($this->lastLine)) {
                    array_pop($this->lines);
                    $line .= ' ';
                    $changes[] = $this->lineNo;
                }
                // wecho($this->lines[count($this->lines)-1]);
                // $l = preg_replace('/href="/', 'class="btn btn-grid-opt" href="', $this->lines[count($this->lines)-1]);
                // wecho($l);
            }
            if ($this->acnt > 1) { // 只有一个的保持原样？？
                wecho("$url line: $this->i_td 操作按钮");
            }
            if ($this->acnt > 2 && $this->with_php) {
                wecho("$url line: $this->i_td 操作按钮 with php");
            }
        }

        // 弹层中的搜索按钮
        if (preg_match('/input.*关键字/', $line)) {
            $this->small_search_btn_mode = 1;
        }
        if (isset($this->small_search_btn_mode) && $this->small_search_btn_mode && preg_match('/label.*button.*搜索/', $line)) {
            wecho("$url line: $this->lineNo 小搜索按钮");
            $line = preg_replace('/<label.*label>/', '<button hidefocus="" class="btn btn-sh btn-sh-fixed" type="submit"><em>搜索</em></button>', $line);
            $changes[] = $this->lineNo;
        }
        if (isset($this->small_search_btn_mode) && $this->small_search_btn_mode 
            && preg_match('/button.*em.*搜索/', $line) 
            && !preg_match('/role.*searchBtn/', $line)
        ) {
            $line = preg_replace('/class="/', 'role="searchBtn" class="', $line);
            $changes[] = $this->lineNo;
        }
        if (preg_match('/<\/div>/', $line)) {
            $this->small_search_btn_mode = 0;
        }

        // datepicker
        if (preg_match('/<input.*class=".*datepicker.*".*role="datePicker".*>/', $line) 
            && !preg_match('/props="/', $line) 
            && preg_match('/id="\d/', $line)
        ) {
            wecho("$url line: $this->lineNo datePicker");
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
        }

        if ($changes) {
            return $line;
        }
    }
}

function wecho($str = '')
{
    $str = str_replace('\\', '/', $str);
    echo iconv('utf-8', 'gbk', $str), PHP_EOL;
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

/**
* 
*/
class Man extends LineProcessor
{
    public function everyLine($line)
    {
        $url = path2url($this->filePath);
        $i = $this->lineNo;
        if (preg_match('/td.*class.*opt/', $line)) {
            $this->state = 1;
            $this->acnt = 0;
        }
        if ($this->state == 1) {
            if (preg_match('/<a.+>/', $line)) {
                $this->acnt++;
            }
            if (preg_match('/<\/td>/', $line)) {
                $this->state = null;
                if ($this->acnt == 1) {
                    if (preg_match('/<a/', $line) && !preg_match('/class.+btn/', $line)) {
                        $line = preg_replace('/href="/', 'class="btn btn-grid-opt" href="', $line);
                        return $line;
                    }
                    wecho("$url $i: 操作按钮 仅一个");
                    if (!trim($this->lastLine)) {
                        array_pop($this->lines);
                        $this->lastLine = $this->lines[count($this->lines)-1];
                        return $line.' ';
                    }
                    if (preg_match('/<a/', $this->lastLine) && !preg_match('/class.+btn/', $this->lastLine)) {
                        $this->lines[count($this->lines)-1] = preg_replace('/href="/', 'class="btn btn-grid-opt" href="', $this->lastLine);
                        return $line.' ';
                    }
                }
            }
        }
    }
}

class DatePickerModifier extends LineProcessor
{
    public function everyLine($line)
    {
        $url = path2url($this->filePath);
        $i = $this->lineNo;
        if (preg_match('/input.*role.*datePicker/', $line) && !preg_match('/id.*dp\d/', $line)) {
            if (1
                && preg_match('/span.*span/', $this->line(1)) 
                && preg_match('/input.*role.*datePicker/', $this->line(2)) 
                && !preg_match('/id.*dp\d/', $this->line(2))
            ) {
                $this->line(2, preg_replace('/role="/', 'id="dp1" props="maxDate:\'#F{$dp.$D(\\\'dp2\\\')}\'" role="', $this->line(2)));
                $line = preg_replace('/role="/', 'id="dp2" props="minDate:\'#F{$dp.$D(\\\'dp1\\\')}\'" role="', $line);
                // wecho($this->line(2));
                // wecho($this->line(1));
                // wecho($line);
                return $line;
            }
        }
    }
}

$m = new DatePickerModifier();
$m->processDir('.');
