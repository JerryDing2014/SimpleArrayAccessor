<?php
namespace Test\Unit\Accessor;

use Accessor\ArrayMutator;

class ArrayMutatorTest extends \PHPUnit_Framework_TestCase
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

    public function testSetWithDefaultArray()
    {
        $mutator = new ArrayMutator();

        $this->assertEquals(array("foo" => "bar"), $mutator->set("foo", "bar")->getArrayCopy());
        $this->assertEquals(array("foo" => $this->array["foo"]), $mutator->set("foo", $this->array["foo"])->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["bar"]["buzz"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.bar.buzz", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0]["a"] = "updated";
        $expected["foo"]["phar"][1]["a"] = "updated";
        $expected["foo"]["phar"][2]["a"] = "updated";
        $expected["foo"]["phar"][3]["a"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[].a", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0]["a"] = "updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[0].a", "updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0] = "updated-updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[0]", "updated-updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"] = "updated-updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar", "updated-updated-updated")->getArrayCopy());
    }

    public function testSetWithGivenArray()
    {
        $mutator = new ArrayMutator($this->array);

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["bar"]["buzz"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.bar.buzz", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0]["a"] = "updated";
        $expected["foo"]["phar"][1]["a"] = "updated";
        $expected["foo"]["phar"][2]["a"] = "updated";
        $expected["foo"]["phar"][3]["a"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[].a", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0]["a"] = "updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[0].a", "updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0] = "updated-updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[0]", "updated-updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"] = "updated-updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar", "updated-updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[]", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["a.b.c"] = "updated";
        $this->assertEquals($expected, $mutator->set("a.b.c", "updated")->getArrayCopy());
    }

    public function testSetWithDeepArray()
    {
        $mutator = new ArrayMutator($this->array);
        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][3]["f"][0]["aa"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[3].f[0].aa", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][3]["f"][0]["aa"] = "updated-updated";
        $expected["foo"]["phar"][3]["f"][1]["aa"] = "updated-updated";
        $expected["foo"]["phar"][3]["f"][3]["aa"] = "updated-updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[3].f[].aa", "updated-updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][3]["f"][0]["bb"] = "updated";
        $expected["foo"]["phar"][3]["f"][1]["bb"] = "updated";
        $expected["foo"]["phar"][3]["f"][3]["bb"] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[3].f[].bb", "updated")->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][3]["f"][2]=array("updated-updated-updated");
        $this->assertEquals($expected, $mutator->set("foo.phar[3].f[2]", array("updated-updated-updated"))->getArrayCopy());

        $expected = $mutator->getArrayCopy();
        $expected["foo"]["phar"][0] = "updated";
        $expected["foo"]["phar"][1] = "updated";
        $expected["foo"]["phar"][2] = "updated";
        $expected["foo"]["phar"][3] = "updated";
        $this->assertEquals($expected, $mutator->set("foo.phar[]", "updated")->getArrayCopy());
    }

    public function testCreate()
    {
        $mutator = ArrayMutator::create($this->array);
        $this->assertInstanceOf('Accessor\ArrayMutator', $mutator);
    }
}
