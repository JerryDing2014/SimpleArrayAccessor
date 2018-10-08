<?php
namespace Test\Unit\Accessor;

use Accessor\ArrayAccessor;

class ArrayAccessorTest extends \PHPUnit_Framework_TestCase
{
    /** @var array */
    private $array;

    public function setUp()
    {
        parent::setUp();

        $this->array = array(
            "foo" => array(
                "bar" => array(1, 2, 3, 'buzz' => 'phar'),
                "phar" => array(
                    array("a" => "b"),
                    array("a" => "c"),
                    array("d" => "e"),
                    array("f" => array(
                        array("aa" => 1),
                        array("bb" => 2),
                        3,
                        array("bb" => 4)
                    )),
                )
            ),
            "a.b.c" => "d"
        );
    }

    public function testGet()
    {
        $accessor = new ArrayAccessor($this->array);

        $this->assertEquals($this->array["foo"], $accessor->get("foo"));
        $this->assertEquals($this->array["foo"]["bar"], $accessor->get("foo.bar"));
        $this->assertEquals($this->array["foo"]["bar"], $accessor->get("foo.bar[]"));
        $this->assertEquals($this->array["foo"]["bar"]["buzz"], $accessor->get("foo.bar.buzz"));
        $this->assertEquals($this->array["foo"]["bar"][1], $accessor->get("foo.bar[1]"));
        $this->assertEquals($this->array["a.b.c"], $accessor->get("a.b.c"));

        $this->assertEquals(array("b", "c", null, null), $accessor->get("foo.phar[].a"));
        $this->assertEquals(array("b", "c", 'not found', 'not found'), $accessor->get("foo.phar[].a", "not found"));
        $this->assertEquals(array(null, null, "e", null), $accessor->get("foo.phar[].d"));
        $this->assertEquals(array("not found", "not found", "e", 'not found'), $accessor->get("foo.phar[].d", "not found"));

        $this->assertEquals(1, $accessor->get("foo.phar[3].f[0].aa"));
        $this->assertEquals(array(null, null, null, array(1, null, null, null)), $accessor->get("foo.phar[].f[].aa"));
    }

    public function testCreate()
    {
        $accessor = ArrayAccessor::create($this->array);
        $this->assertInstanceOf('Accessor\ArrayAccessor', $accessor);
    }
}
