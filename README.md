# SimpleArrayAccessor
a lib to set/get array value with dot notation

# Usage
````
use ArrayAccessor\DotNotationAccessor;

$accessor = new DotNotationAccessor(array(
    "foo" => array(
        "bar" => array(1, 2, 3, 'buzz' => 'phar')
    )
));

var_dump($accessor->get('foo'));
// output
// array(
//    "bar" => array(1, 2, 3, 'buzz')
// )

var_dump($accessor->get('foo.bar'));
// output
// array(1, 2, 3, 'buzz')

var_dump($accessor->get('foo.bar[1]'));
// output
// 2

var_dump($accessor->get('foo.bar.buzz'));
// output
// phar
