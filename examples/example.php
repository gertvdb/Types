<?php

use Gertvdb\Types\Array\Array\FixedArray;
use Gertvdb\Types\Array\Dictionary\Dictionary;
use Gertvdb\Types\Array\HashSet\HashSet;
use Gertvdb\Types\Int\IntValue;
use Gertvdb\Types\Sorting\SortDirection;
use Gertvdb\Types\Sorting\SortOrder;
use Gertvdb\Types\String\StringValue;
use Gertvdb\Types\Array\HashSet\ScalarHashSet;

$hashSet = HashSet::empty(IntValue::class);

$hashSet->add(IntValue::fromInt(2));
$hashSet->add(IntValue::fromInt(14));
$hashSet->add(IntValue::fromInt(2));

echo $hashSet->toArrayValue()->key_exists(2);


$hashMap = Dictionary::empty();

$hashMap->add(IntValue::fromInt(2), StringValue::fromString('Gert'));
$hashMap->add(IntValue::fromInt(3), StringValue::fromString('Jos'));

echo $hashMap->count();

$scalesHashSet = ScalarHashSet::empty('int');
$scalesHashSet->add(3);
$scalesHashSet->add(5);
$scalesHashSet->add(7);

$scalesHashSet->sort(function (int $a, int $b) {
   return $a <=> $b;
});



$scalesHashSet->sort(function (int $a, int $b) {
   return SortDirection::apply(
       SortOrder::fromComparison($a <=> $b),
       SortDirection::DESC
   );
});


$fixed = FixedArray::fromArray([1,2,3,5,7,8], 'int');
echo $fixed->get(4);
$fixed->set(4, 9);
echo $fixed->get(4);



$stringHash = StringHashSet::caseInsensitive();
$stringHash->add('gert');
$stringHash->add('Jos');
$stringHash->add('Gert');
$stringHash->toArray();

