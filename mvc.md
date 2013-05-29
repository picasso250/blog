Why MVC in PHP
=================

Generally, we will seperate logic(PHP code) and template(HTML code) into different files. If you are wrighting a blog web site, it likes as below in your project:

index.php
```php
<?php
$data = fetch_from_db_as_array("SELECT * FROM blog WHERE ...");
include 'index.html';
```

index.html
```html
<body>
    <h1><?= $data['title'] =></h1>
    <p><?= $data['content'] =></p>
</body>
```

Well, it fits for you. but when the project grow, it starts to sucks to write SQL everywhere. So maybe you will write some functions such as:

```php
function get_blog($id); // will be used in blog page and edit page
```

May be you will put these functions into a single file. These functions are called **Model**. Your previous file contain logic code which see what user input and decide what to output is called **Controller**. And you template will be called **View**.
That's where cames the MVC.

Once you extract a layer called Model, you will find something new from this new perspective.

Let's say your projects continues to grow(you want the feature of commenting), and you will have functions like:
```php
function get_comments_of($blog_id, $max_count); // will be used in blog list page and blog page
```

All you want to do is to fetch something from database. This purpose canbe well extract into one concept, which is called **Ojbect**.
Yes, people always talk about Object Oriented Programing, that's where it comes.

So if you use OO, your code will be like:
```php
$data = Blog::get($id);
$comments = Comment::getListByBlog($blogId);
Blog::del($id);
$id = Blog::add($tile, $content);
```

In order to write code like this, you should write some class files. And, you make 100 line code for blog, and another 100 line code for comment. In fact, you will find majority of these lines are similar, as most of them are CURD of database. Yes, you are repeating yourself. And, once for a while, you'd expect someone has write it for you.
Someone did, and they call it ORM. With ORM, you can directly write code like this:

```php
<?php
$book = BookQuery::create()->findPK(123); // retrieve a record from a database
$book->setName('Don\'t be Hax0red!'); // modify. Don't worry about escaping
$book->save(); // persist the modification to the database

$books = BookQuery::create()  // retrieve all books...
  ->filterByPublishYear(2009) // ... published in 2009
  ->orderByTitle()            // ... ordered by title
  ->joinWith('Book.Author')   // ... with their author
  ->find();
foreach($books as $book) {
  echo  $book->getAuthor()->getFullName();
}
```
The code above is from [index of Propel](http://propelorm.org/).

You can use a mature lib of ORM, or you can, as I did, write a ORM.


