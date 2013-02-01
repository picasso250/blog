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
}

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