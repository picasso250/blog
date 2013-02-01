| Java | Objective-C |
|-----:|:------------|
| Interface | Protocol |
| this | self |

**Method Call**

```java
o.doSomething();
o.give(something, somebody);
```

```objc
[o doSomething];
[o give:something to:somebody];
```

**New Object**

```java
o = new Obj();
p = new Obj(q);
```

```objc
o = [[Obj alloc] init];
p = [[Obj alloc] initWithAnotherObj: q];
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