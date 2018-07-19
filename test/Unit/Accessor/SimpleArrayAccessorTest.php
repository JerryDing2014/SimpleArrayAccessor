<?php
namespace Test\Unit\Accessor;

use Accessor\SimpleArrayAccessor;

class SimpleArrayAccessorTest extends \PHPUnit_Framework_TestCase
{
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
                    array("d" => "e")
                )
            ),
            "a.b.c" => "d"
        );
    }

    public function testGet()
    {
        $accessor = new SimpleArrayAccessor($this->array);

        $this->assertEquals($this->array["foo"], $accessor->get("foo"));

        $this->assertEquals($this->array["foo"]["bar"], $accessor->get("foo.bar"));
        $this->assertEquals($this->array["foo"]["bar"], $accessor->get("foo.bar[]"));
        $this->assertEquals($this->array["foo"]["bar"]["buzz"], $accessor->get("foo.bar.buzz"));
        $this->assertEquals($this->array["foo"]["bar"][1], $accessor->get("foo.bar[1]"));
        $this->assertEquals($this->array["a.b.c"], $accessor->get("a.b.c"));

        $this->assertEquals(array("b", "c", null), $accessor->get("foo.phar[].a"));
        $this->assertEquals(array("b", "c", 'not found'), $accessor->get("foo.phar[].a", "not found"));
        $this->assertEquals(array(null, null, "e"), $accessor->get("foo.phar[].d"));
        $this->assertEquals(array("not found", "not found", "e"), $accessor->get("foo.phar[].d", "not found"));
    }

    public function testHas()
    {
        $accessor = new SimpleArrayAccessor($this->array);

        $this->assertTrue($accessor->has("foo"));
        $this->assertTrue($accessor->has("foo.bar"));
        $this->assertTrue($accessor->has("foo.bar[1]"));
        $this->assertTrue($accessor->has("foo.bar.buzz"));
        $this->assertTrue($accessor->has("foo.phar[].a"));
        $this->assertTrue($accessor->has("foo.phar[].d"));

        $this->assertFalse($accessor->has("foo.bar.buzz1"));
        $this->assertFalse($accessor->has("foo.buzz1"));
        $this->assertFalse($accessor->has("foo.bar.a"));

        $this->assertFalse($accessor->has("foo.phar[].e"));
    }

    public function testSetWithDefaultArray()
    {
        $accessor = new SimpleArrayAccessor();

        $this->assertEquals(array("foo" => "bar"), $accessor->set("foo", "bar")->getArray());
        $this->assertEquals(array("foo" => $this->array["foo"]), $accessor->set("foo", $this->array["foo"])->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["bar"]["buzz"] = "updated";
        $this->assertEquals($expected, $accessor->set("foo.bar.buzz", "updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0]["a"] = "updated";
        $expected["foo"]["phar"][1]["a"] = "updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[].a", "updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0]["a"] = "updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[0].a", "updated-updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0] = "updated-updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[0]", "updated-updated-updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"] = "updated-updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar", "updated-updated-updated")->getArray());
    }

    public function testSetWithGivenArray()
    {
        $accessor = new SimpleArrayAccessor($this->array);

        $expected = $accessor->getArray();
        $expected["foo"]["bar"]["buzz"] = "updated";
        $this->assertEquals($expected, $accessor->set("foo.bar.buzz", "updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0]["a"] = "updated";
        $expected["foo"]["phar"][1]["a"] = "updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[].a", "updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0]["a"] = "updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[0].a", "updated-updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"][0] = "updated-updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar[0]", "updated-updated-updated")->getArray());

        $expected = $accessor->getArray();
        $expected["foo"]["phar"] = "updated-updated-updated";
        $this->assertEquals($expected, $accessor->set("foo.phar", "updated-updated-updated")->getArray());

        $expected = $accessor->getArray();
        $expected["a.b.c"] = "updated";
        $this->assertEquals($expected, $accessor->set("a.b.c", "updated")->getArray());
    }
}
