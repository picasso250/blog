PHP 中的引用
---------------

先来看看下面的 PHP 代码：

```php
$a = "this";
$b = $a;
```

其实，你一定很好奇 PHP 在执行这段代码的时候发生了什么事情。

笼统的说起来，在 PHP 语言中，所有的变量都有两个属性：名和值。变量的名字肯定是个字符串，以字符串的形式保存。变量的值稍微复杂一些，值是有类型的，还有是否是被引用的。在 PHP 中，变量以 zval 的形式保存起来， zval 中保存有变量的值和类型等信息。可以用 debug\_zval_dump() 函数来查看变量的 zval 表示形式的信息。

zval 这个名字，大概是 zend value 的缩写。

```c
struct zval {
	value;
	type;
	is_ref;
	refcount;
}
```

zval 保存了变量的值和类型，那么 is_ref 和 refcount 是什么意思呢？稍安勿躁，且听我一一道来。 is_ref 代表这个值是否是被引用的， refcount 则表示有多少个变量名指向这个值。

现在我们回头看看上面的代码。当 `$a = "this";` 执行时，一个表示 `"this"` 的 zval 被建立。变量名 `a` 指向这个 zval 。当 `$a = $b;` 执行时，系统并不会给变量 `b` 重新分配 zval ，而是让他指向同一个 zval ，并且让这个 zval 的 refcount 加一。最初的分配这个 zval 时，只有 `a` 指向它，因此，它的 refcount 是 1 。而当 `b` 也指向它的时候，这个 zval 的 refcount 就是 2 了。

我们可以看到， PHP 比较智能的节省了空间。

假设我们现在继续执行以下语句。

```php
unset($b);
unset($a);
```

当执行 `unset($b)` 时，系统会将变量名 `b` 移除。对 `b` 所指向的 zval ， refcount 减一。 现在，这个 zval 的 refcount 又回到了 1 。

当执行 `unset($a);` 时，系统将变量名 `a` 移除，对 `a` 所指向的 zval 的 refcount 减一。这个 zval 的 refcount 减为 0 了。 refcount 为 0 也就意味着 这个 zval 不再被任何变量名引用，它的生命也就走到了尽头， PHP 会回收这个 zval 。

有的人还知道 PHP 里的引用操作符 `=&` ，他还认为，引用操作符可以节省内存。这个真不一定，要看情况。

```php
$a = 'this';
$b = $a;
$c =& $b;
```

上面的语句前两行我们很熟悉，最后一行 `$c =& $b;` 执行之前， 变量 `a` `b` 共享一个 zval ，这个 zval 的 refcount 是 2 。 `$c =& $b;` 执行的时候，系统会给变量 `b` 重新分配一个 zval ，但并不剥夺变量 `a` 的 `zval`，然后将变量 `c` 指向 `b` 的 zval 。因为 `c` 是引用的 `b` ，所以，这个 zval 的 is_ref 是 1 而不是普通变量的 0 。对于 is_ref 为 1 的变量， refcount 的值就没有意义了。

从上面可以看出， `$a $b $c` 需要两个 zval ，而如果不用引用赋值的话，一个 zval 就够了。如果不是必须用引用，就不要用引用，让 PHP 替你管理内存吧。

更多请看：

[References in PHP: An In-Depth Look](http://derickrethans.nl/talks/phparch-php-variables-article.pdf)