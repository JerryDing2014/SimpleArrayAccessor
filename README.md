# SimpleArrayAccessor
a lib to set/get array value with dot notation

# Usage
get value

```php
use Accessor\SimpleArrayAccessor;

$accessor = new SimpleArrayAccessor(array(
    "foo" => array(
        "bar" => array(1, 2, 3, 'buzz' => 'phar'),
        "phar" => array(
            array("a" => "b"),
            array("a" => "c"),
            array("d" => "e")
        )
    ),
    "a.b.c" => "d"
));

var_dump($accessor->get('foo'));
// output:
// array(
//    "bar" => array(1, 2, 3, 'buzz'),
//    "phar" => array(
//         array("a" => "b"),
//         array("a" => "c"),
//         array("d" => "e")
//     )
// )

var_dump($accessor->get('foo.bar[]'));
// output: array(1, 2, 3, 'buzz')
// equivelant to "$accessor->get('foo.bar')"

var_dump($accessor->get('foo.bar'));
// output: array(1, 2, 3, 'buzz')

var_dump($accessor->get('foo.bar[1]'));
// output: 2

var_dump($accessor->get('foo.bar.buzz'));
// output: 'phar'

var_dump($accessor->get('a.b.c'));
// output: 'd'

var_dump($accessor->get('foo.phar[].a'));
// output: array("b", "c", null)

var_dump($accessor->get('foo.phar[].a', 'not found'));
// output: array("b", "c", 'not found')
```
set value

```php
use Accessor\SimpleArrayAccessor;

// with no given array
$accessor = new SimpleArrayAccessor();
var_dump($accessor->set("foo", "bar")->getArray());
// output: 
// array(
//      "foo" => "bar"
// )

$fooValue = array(
    "bar" => array(1, 2, 3, 'buzz' => 'phar'),
    "phar" => array(
        array("a" => "b"),
        array("a" => "c"),
        array("d" => "e")
    )
);
var_dump($accessor->set("foo", $fooValue)->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'phar'),
//             "phar" => array(
//                 array("a" => "b"),
//                 array("a" => "c"),
//                 array("d" => "e")
//             )
//      )
// )

var_dump($accessor->set("foo.bar.buzz", "updated")->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'updated'),
//             "phar" => array(
//                 array("a" => "b"),
//                 array("a" => "c"),
//                 array("d" => "e")
//             )
//      )
// )

var_dump($accessor->set("foo.phar[].a", "updated")->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'updated'),
//             "phar" => array(
//                 array("a" => "updated"),
//                 array("a" => "updated"),
//                 array("d" => "e")
//             )
//      )
// )

var_dump($accessor->set("foo.phar[0].a", "updated-updated")->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'updated'),
//             "phar" => array(
//                 array("a" => "updated-updated"),
//                 array("a" => "updated"),
//                 array("d" => "e")
//             )
//      )
// )

var_dump($accessor->set("foo.phar[0]", "updated-updated-updated")->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'updated'),
//             "phar" => array(
//                 "updated-updated-updated",
//                 array("a" => "updated"),
//                 array("d" => "e")
//             )
//      )
// )

var_dump($accessor->set("foo.phar", "updated-updated-updated")->getArray());
// equivelant to $accessor->set("foo.phar[]", "updated-updated-updated")
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'updated'),
//             "phar" => "updated-updated-updated"
//      )
// )


// with given array
$array = array(
    "foo" => array(
     "bar" => array(1, 2, 3, 'buzz' => 'phar'),
     "phar" => array(
         array("a" => "b"),
         array("a" => "c"),
         array("d" => "e")
     )
    ),
    "a.b.c" => "d"
);
$accessor = new SimpleArrayAccessor($array)

var_dump($accessor->set("a.b.c", "updated")->getArray());
// output:
// array(
//      "foo" => array(
//             "bar" => array(1, 2, 3, 'buzz' => 'phar'),
//             "phar" => array(
//                 array("a" => "b"),
//                 array("a" => "c"),
//                 array("d" => "e")
//             )
//            ),
//       "a.b.c" => "updated"
// )
```

Note: the key of element in array is better not to contain "." to avoid ambiguous meaning. However if "."
is a compulsory character, please use it in root level but not child level, as below:

```php
$arrayWithRegconisableKeys = array(
    "foo" => "bar",
    "buzz" => array(1, 2, 3)
    "a.b.c" => "d"
);

$arrayWithUnregconisableKeys = array(
    "foo" => array(
        "a.b.c" => "d"
    )
);
```
