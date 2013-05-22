不用 count，计算一维数组大小
==========================

不用 `count()` `sizeof` `for` `foreach` `while` 等，计算一个以为数组的大小。使用 PHP 语言。

.

.

.

.

.

.

.

.

.

.

.

.

.

.

.

.

.

.

答案：

因为 PHP 非常灵活，因此答案也是非常的多。

```php
<?
// 使用递归（lisp入门必学）
function my_count($a)
{
    if (!$a)
        return 0;
    array_pop($a);
    return 1 + my_count($a);
}

// 使用字符串（巧妙）
function my_count($a)
{
    return $a ? strlen(implode(',', $a)) - strlen(implode('', $a)) + 1 : 0;
}

// 使用函数式编程中的 reduce
function my_count($a)
{
    return array_reduce($a, function($r,$e){ return $r + 1; }, 0);
}

// 为达目的不择手段的
function my_count($a)
{
    return array_sum(array_map(function() { return 1; }, $a));
}

// 使用数组指针的
function my_count($a)
{
    if(!$a)
        return 0;
    $a = array_values($a);
    reset($a);
    $f = key($a);
    end($a);
    return key($a) + 1 - $f;
}

// callback 一招鲜吃遍天的
// 只要接受 callback 的 array 系函数都可以用这招
function my_count($a)
{
    global $i = 0;
    array_walk($a, function () {
        $i++;
    });
    return $i;
}

// 使用 goto 的
function my_count($a){
    $i = 0;
    begin:
    if (key($a) !== null) {
        $i++;
        next($a);
        goto begin;
    }
    return $i;
}
```
