# Mock static methods

...and a bit more ;)

# Goals

- easy mocking
- make any changes revertible
- revert changes automatically

# Targets

There are several classes that will do the work:

- To use with PHPUnit:
  - `MockedMethod` to mock method "globally" and use PHPUnit's `*Mockers` - `expects/willReturn/willReturnCallback/...`
- For class methods:
  - `MockedClassMethod` to mock any class with closure
  - `UndefinedClassMethod` to make method disappear
- For class constants:
  - `MockedClassConstant`
  - `UndefinedClassConstant`
- For global constants:
  - `MockedGlobalConstant`
  - `UndefinedGlobalConstant`

# Install

```shell
composer require --dev qratorlabs/smocky
```

## MockedMethod

The main target is to make mocking static methods easy and feels-n-looks like using PHPUnit.

```php
public function testSomeTest(): void {
    $methodMock = new MockedMethod($this, SomeClass::class, 'someMethod', once());
    $methodMock->getMocker()->willReturn(10);

    SomeClass::someMethod(); // will return `10`
    
    SomeClass::someMethod(); // will cause error (expectation - one call)
}
```

### Note

There is a workaround that ensures that any (defined) children of class, which method is mocking, have its own method,
defined by user or mocked (by Smocky - closure that calls parent).

Example for code that will fail without this workaround following code will end up with `Segmentation fault: 11`

```php
class Base
{
    public static function methodName()
    {
        return 'something';
    }
}

class Child extends Base
{

}

// simulating PHPUnit test case
(new class('test') extends \PHPUnit\Framework\TestCase {

    public function test(): void
    {
        // child should instanced (or loaded any other way)
        $child = new Child();
        // mocking method of base class
        $method = new MockedClassMethod(Base::class, 'methodName');
        // at least one call should be made
        Base::methodName();
    }
})->run();
```

# Trivia

## Revertible changes

All changes are made revertible by using internal storage and `__destruct` methods.

## Drawbacks

Thing to keep in mind before using:

- Mocking anything will hit memory consumption (ex. to preserve changes)
- Mocking methods will hit performance (a bit)
- To mock static class we must check (and mock) children of mocking class

## Todo

- Add support for mocking global functions

# Powered by

- [runkit7](https://github.com/runkit7/runkit7)
- [phpunit](https://github.com/sebastianbergmann/phpunit)

# Tested with

- [phpunit](https://github.com/sebastianbergmann/phpunit)
- [phpstan](https://github.com/phpstan/phpstan)
- [PHP_CodeSniffer](https://github.com/squizlabs/PHP_CodeSniffer)
