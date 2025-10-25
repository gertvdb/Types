[project-name]: Types
[project-url]: https://github.com/gertvdb/Types
[project-build]: https://github.com/gertvdb/Types/actions/workflows/quality_assurance.yaml/badge.svg
[project-tests]: https://github.com/gertvdb/Types/blob/main/badge-coverage.svg

# Types
![Build][project-build]
![Tests][project-tests]
[![Software License][ico-license]](LICENSE.md)

Strongly-typed, immutable value objects for PHP. This library wraps native primitives and common data structures in small, focused classes with predictable behavior and convenient APIs.

- Immutable by default: all mutating operations return new instances
- Strong typing: each class validates input at construction
- Useful collections: typed arrays, sets, lists, dictionaries, stacks, queues
- Practical date/time and i18n helpers

## Installation

```bash
composer require gertvdb/types
```

## Quick start

Below are small examples for each main class in the library. For more, see the test suite under /tests which serves as executable documentation.

### Primitive value objects

- IntValue
```php
use Gertvdb\Types\Int\IntValue;

$age = IntValue::fromInt(42);
$parsed = IntValue::fromString(' 00123 '); // 123
$len = $parsed->length(); // 3
$asInt = $age->toInt();   // 42
```

- FloatValue
```php
use Gertvdb\Types\Float\FloatValue;

$price = FloatValue::create(19.99);
$asFloat = $price->toFloat();
```

- StringValue
```php
use Gertvdb\Types\String\StringValue;

// Constructor normalizes and trims invisible characters
$name = StringValue::fromString(' Ada ');
$upper = $name->uppercase();
$lower = $name->lowercase();
$len = $upper->length();
```

- BooleanValue
```php
use Gertvdb\Types\Boolean\BooleanValue;

$flag = BooleanValue::fromBool(true);
if ($flag->isTrue()) {
    // ...
}
```

- Ranges and bounded values
```php
use Gertvdb\Types\Int\BoundedIntValue;
use Gertvdb\Types\Int\IntRange;
use Gertvdb\Types\Float\BoundedFloatValue;
use Gertvdb\Types\Float\FloatRange;

$intRange = IntRange::from(0, 100);
$score = BoundedIntValue::create(95, $intRange); // throws if out of bounds

$floatRange = FloatRange::from(0.0, 1.0);
$ratio = BoundedFloatValue::create(0.75, $floatRange);
```

### Collections

- ArrayValue (immutable wrapper around native array)
```php
use Gertvdb\Types\Array\ArrayValue;

$numbers = ArrayValue::fromArray([1, 2, 3]);
$double = $numbers->map(fn($n) => $n * 2); // [2,4,6]
$even = $double->filter(fn($n) => $n % 2 === 0); // [2,4,6]
```

- FixedArray (typed, fixed-size array)
```php
use Gertvdb\Types\Array\Array\FixedArray;

$fixed = FixedArray::fromArray([1, 2, 3], 'int');
$second = $fixed->get(1); // 2
$fixed2 = $fixed->set(1, 5); // new instance, [1,5,3]
```

- ListValue and SortedList
```php
use Gertvdb\Types\Array\List\ListValue;
use Gertvdb\Types\Array\List\SortedList;

$list = ListValue::empty('int')->append(3)->append(1)->append(2);
$sorted = SortedList::fromList($list)->sort(fn(int $a, int $b) => $a <=> $b);
```

- HashSet, ScalarHashSet and SortedHashSet
```php
use Gertvdb\Types\Array\HashSet\HashSet;
use Gertvdb\Types\Array\HashSet\ScalarHashSet;
use Gertvdb\Types\Array\HashSet\SortedHashSet;
use Gertvdb\Types\Int\IntValue;

// HashSet of value objects (must implement IHashable)
$set = HashSet::empty(IntValue::class)
    ->add(IntValue::fromInt(2))
    ->add(IntValue::fromInt(2));
$count = $set->count(); // 1

// ScalarHashSet for primitives
$ids = ScalarHashSet::empty('int')->add(3)->add(5);

// SortedHashSet with a comparator
$sortedIds = SortedHashSet::fromSet($ids)
    ->sort(fn(int $a, int $b) => $a <=> $b);
```

- Dictionary and SortedDictionary
```php
use Gertvdb\Types\Array\Dictionary\Dictionary;
use Gertvdb\Types\Array\Dictionary\SortedDictionary;

// Keys can be value-objects or scalars ('int'|'string')
$dict = Dictionary::empty('int', 'string')
    ->add(1, 'one')
    ->add(2, 'two');

$two = $dict->get(2); // 'two'

$sortedDict = SortedDictionary::fromDictionary($dict)
    ->sortByKeys(fn(int $a, int $b) => $a <=> $b);
```

- Stack and Queue
```php
use Gertvdb\Types\Array\Stack\Stack;
use Gertvdb\Types\Array\Queue\Queue;

$stack = Stack::empty('string')->push('a')->push('b');
$top = $stack->peek(); // 'b'
$stack2 = $stack->pop(); // new instance without 'b'

$queue = Queue::empty('int')->enqueue(1)->enqueue(2);
$front = $queue->peek(); // 1
$queue2 = $queue->dequeue(); // new instance without 1
```

### Date & Time
```php
use Gertvdb\Types\DateTime\DateOnly;
use Gertvdb\Types\DateTime\Time;
use Gertvdb\Types\DateTime\DateTime;
use Gertvdb\Types\DateTime\DateOnlyRange;
use Gertvdb\Types\DateTime\FixedClock;

$clock = new FixedClock('2025-01-15T12:00:00+00:00');
$today = DateOnly::fromString('2025-01-15');
$time = Time::fromHms(14, 30, 0);
$dt = DateTime::combine($today, $time);

$range = DateOnlyRange::from(DateOnly::fromString('2025-01-01'), DateOnly::fromString('2025-01-31'));
$contains = $range->contains($today); // true
```

### Internationalization
```php
use Gertvdb\Types\I18n\Locale;
use Gertvdb\Types\I18n\Language;

$nl = Locale::fromString('nl-BE');
$lang = Language::fromIso639_1('nl');
```

### Ordering helpers
```php
use Gertvdb\Types\Order\Compare;
use Gertvdb\Types\Order\Direction;

$cmp = Compare::from(fn($a, $b) => $a <=> $b);
$result = Direction::apply($cmp->compare(1, 2), Direction::DESC); // invert comparison
```

### Helpers
```php
use function Gertvdb\Types\hash;
use function Gertvdb\Types\isOfType;

isOfType(5, 'int');      // true
isOfType('x', 'string'); // true
hash(123);               // '123'
```

## Development

- Tests: `composer test`
- Static analysis: `composer phpstan`

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email gertvandenbuijs at hotmail dot com instead of using the issue tracker.

## License

The Lesser GPL version 3 or later. Please see [License File](LICENSE.md) for more information.

[link-owner]: https://github.com/gertvdb
[link-contributors]: ../../contributors
[ico-license]: https://img.shields.io/badge/License-AGPLv3-green.svg?style=flat-square


