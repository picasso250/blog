PHP 装逼指南
===================

用 PHP 装逼是得天独厚的，因为 PHP 和 perl 一样，都崇尚“做同一种事情不止一种方法”。而用 Java 装逼成功的人，大概已经是真的牛逼了。

不要以为装逼很容易，装逼的重点在于一个装字。一个真正牛逼的人不一定擅长装逼。一个擅长装逼的人，总是能让别人以为自己很厉害。秘诀就是，一定要让人半懂不懂。如果你写的代码有 100% 被看懂了，别人会不屑一顾，如果全都不懂呢，别人根本就没有震撼的感觉。我曾经看过一篇涉及 PHP 源码的文章，里面的东西非常高深。结果一看评论非常傻眼，底下一大堆博主的同事的评论都是：“老张就爱写一些大家看不懂的东西”。这位老张是真牛逼，以他的资质，装逼是轻而易举，然而他不会装逼。

装逼指南第一条：缩短语句
----------------------

我们才不会写 

```php
if ($you->laugh_at($I)) {
    $I->kill($you);
}
```

我们会写

```php
$you->laugh_at($I) and $I->kill($you);
```

我们也不会写

```php
if (!$you->eat($shit)) {
    $I->kill($you);
}
```

我们会写

```php
$you->eat($shit) or $I->kill($you);
```

装逼指南第二条：换种方式
------------------------

别人这样写 for 循环

```php
for ($i=0; $i<$count; $i++) {
    // ...
}
```

我们这样写

```php
$i = $count;
while ($i--) {
    // ...
}
```
别人这样写

```php
$ret = array();
foreach ($arr as $e) {
    $ret[] = $e->prop;
}
```

我们这样写

```php
$ret = array_map(function ($e) {return $e->prop;}, $arr);
```
当然，进阶版就是一定要多用 array_ 系的函数，把 PHP 当成 lisp 来写，但就是不缩进，直到别人都看不出你写的是什么为止

所以，遇到这种情况
```php
$ret = array();
foreach ($arr as $k=>$v) {
    $ret[] = "$k:$v";
}
$str = implode(';', $ret);
```
我们一定要写

```php
$ret = array_combine(array_keys($arr), array_map(function ($e) {return $e->prop;}, $arr));
```