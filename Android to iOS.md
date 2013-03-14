**Concepts**

| Java | Objective-C |
|-----:|:------------|
| Interface | Protocol |

**Keyword**

| Java | Objective-C |
|-----:|:------------|
| this | self |
| bool | BOOL |
| true | YES |
| false | NO |
| null | nil |

**Method Call**

```java
o.doSomething();
o.give(something, somebody);
o.getA().foo();
```

```objc
[o doSomething];
[o give:something to:somebody];
[[o getA] foo];
```

**New Object**

```java
o = new Obj();
p = new Obj(q);
```

```objc
o = [[Obj alloc] init];
p = [[Obj alloc] initWithAnotherObj:q];
```

**String**

```java
String s = "I am a string.";
```

```objc
NSString *s = @"I am a string.";
```

**Property(Getters and Setters)**

```java
o.setName(name);
name = o.getName();
```

```objc
// these 2 lines below will fired getter and setter methods automaticly
o.name = name;
name = o.name;
```

**Define a class**

```java
class MyClass extends Object {
	private int data;
	private String name;

	public MyClass(String name) {
		this.name = name;
	}
	public static MyClass instance(name) {
		return new MyClass(name);
	}
}
```

```objc
// ==== file: MyClass.h ====
@interface MyClass : NSObject
{
	int data;
	NSString *name;
}
- (id)initWithString:(NSString *)aName;
+ (MyClass *)myClassWithString:(NSString *)aName;
@end;
```

```objc
// ==== file: MyClass.m ====
#import "MyClass.h"

@implementation MyClass
- (id)initWithString:(NSString *)aName
{
    // code goes here
}
 
+ (MyClass *)myClassWithString:(NSString *)aName
{
    // code goes here
}
@end
```

代理

代理的好处就是可以将程序中自定义的那一部分集中到一起。

命名规则

简明：简洁、明白

| Code | 评价 |
|:-----|:----|
| insertObject:atIndex: | 好 |
| insert:at: | 不清楚，插入什么？ |
| removeObjectAtIndex: | 好 |
| removeObject: | 好，因为是根据指针删除的 |
| remove: | 不清楚，删除什么？ |

不要乱用缩写

| Code | 评价 |
|:-----|:----|
| destinationSelection | 好 |
| destSel | 不好 |
| setBackgroundColor: | 好 |
| setBkgdColor: | 不好 |

一些知名的缩写

| 缩写 | 全拼 |
|:-----|:-----|
| alloc | Allocate |
| alt | Alternate |
| app | Application |
| calc | Calculate |
| dealloc | Deallocate |
| func | Function |
| horiz | Horizontal |
| info | information |
| init | Initialize |
| int | Integer |
| max | Maximum |
| min | Minimum |
| msg | Message |
| nib | Interface Builder archive |
| pboard | PasteBoard |
| rect | Rectangle |
| Rep | Representation |
| temp | Temporary |
| vert | Vertical |
