<?php

use PHPUnit\Framework\TestCase;

use Lightscale\ArrayUtils as A;

final class ArrayUtilsTest extends TestCase {

    public function testGet() {
        $this->assertNull(A::get(null, null));
        $this->assertNull(A::get([], null));
        $this->assertNull(A::get([1, 2, 3], null));
        $this->assertNull(A::get(['test' => 1, 'test2' => 'test'], null));


        $this->assertEquals(1, A::get(null, null, 1));
        $this->assertEquals(1, A::get([], 0, 1));
        $this->assertEquals(1, A::get([1, 2, 3], 4, 1));
        $this->assertEquals(1, A::get([1, [4, 5, 6], 3], [1, 3], 1));
        $this->assertEquals(1, A::get(['test' => 1, 'test2' => 'test'], null, 1));

        $this->assertEquals(2, A::get([1, 2, 3], 1));
        $this->assertEquals(6, A::get([1, [4, 5, 6], 3], [1, 2]));
        $this->assertEquals('test', A::get(['test' => 1, 'test2' => 'test'], 'test2'));
        $this->assertEquals('test', A::get(['test' => 1, 'test2' => 'test'], ['test2']));
        $this->assertEquals(1, A::get(['test' => 1, 'test2' => 'test'], 'test'));

        $this->assertEquals([1, 2, 3], A::get(['test' => 1, 'test2' => [1, 2, 3]], ['test2']));
        $this->assertEquals(3, A::get(['test' => 1, 'test2' => [1, 2, 3]], ['test2', 2]));
        $this->assertEquals('test4', A::get(['test' => 1, 'test2' => [1, ['test3' => 'test4'], 3]], ['test2', 1, 'test3']));

        $this->assertEquals('test4', A::get((object) ['test' => 1, 'test2' => [1, (object) ['test3' => 'test4'], 3]], ['test2', 1, 'test3']));
    }

    public function testGetter() {
        $arr = [
            'test1' => 123,
            'test2' => 'test3',
            'test4' => true,
            'test5' => false,
            'test6' => []
        ];
        $g = A::getter($arr);

        $this->assertIsCallable($g);
        $this->assertEquals(123, $g('test1'));
        $this->assertEquals('test3', $g('test2'));
        $this->assertTrue($g('test4'));
        $this->assertFalse($g('test5'));
        $this->assertIsArray($g('test6'));

        $g = A::getter($arr, function($val) {
            return strval($val);
        });
        $this->assertEquals('123', $g('test1'));
        $this->assertEquals('test3', $g('test2'));
        $this->assertEquals('1', $g('test4'));
        $this->assertEquals('', $g('test5'));
    }

    public function testSelect() {
        $arr = [
            'test1' => 123,
            'test2' => 'test3',
            'test4' => '',
            'test5' => 'test7',
            'test6' => 'test8'
        ];

        $r = A::select($arr, []);
        $this->assertIsArray($r);
        $this->assertEmpty($r);

        $r = A::select($arr, 'test1');
        $this->assertIsArray($r);
        $this->assertEquals(['test1' => 123], $r);

        $this->assertEquals([
            'test2' => 'test3',
            'test5' => 'test7'
        ], A::select($arr, ['test2', 'test5']));
    }

    public function testDissoc() {
        $arr = [
            'test1' => 123,
            'test2' => 'test3',
            'test4' => '',
            'test5' => 'test7',
            'test6' => 'test8'
        ];

        $r = A::dissoc([], 'test');
        $this->assertIsArray($r);
        $this->assertEmpty($r);
        $this->assertIsArray(A::dissoc($arr, 'test1'));
        $this->assertEquals($arr, A::dissoc($arr, []));
        $this->assertEquals([
            'test5' => 'test7',
            'test6' => 'test8'
        ], A::dissoc($arr, ['test1', 'test2', 'test4']));
    }

    public function testFlatten() {
        $this->assertIsArray(A::flatten([]));
        $this->assertEquals([1, 2, 3], A::flatten([1, 2, 3]));
        $this->assertEquals([1, 2, 3], A::flatten(['test1' => 1, 'test2' => 2, 'test3' => 3]));
        $this->assertEquals([1, 2, 3, 4, 5], A::flatten([1, 2, [3, 4, 5]]));
        $this->assertEquals([1, 2, 3, 6, 7, 5], A::flatten([1, 2, [3, [6, 7], 5]]));
    }

    public function testToPairs() {
        $arr = [
            'test1' => 123,
            'test2' => 'test3',
            'test4' => true
        ];

        $this->assertIsArray(A::topairs([]));
        $this->assertEquals([['test1', 123], ['test2', 'test3'], ['test4', true]], A::topairs($arr));
        $this->assertEquals([[0, 1], [1, 2], [2, 3]], A::topairs([1, 2, 3]));
    }

    public function testGroupBy() {
        $arr = [
            ['id' => '1'],['id' => '2'], ['id' => '3'], ['id' => '2'], ['id' => '3']
        ];
        $this->assertIsArray(A::groupBy([], ''));
        $this->assertIsArray(A::groupBy($arr, 'id'));

        $result = [
            '1' => [['id' => '1']],
            '2' => [['id' => '2'], ['id' => '2']],
            '3' => [['id' => '3'], ['id' => '3']]
        ];
        $this->assertEquals($result, A::groupBy($arr, 'id'));

        $result = [
            1 => [['id' => '1']],
            2 => [['id' => '2'], ['id' => '2']],
            3 => [['id' => '3'], ['id' => '3']]
        ];
        $this->assertEquals($result, A::groupBy($arr, 'id', 'intval'));
    }

    public function testKey() {
        $arr = [
            ['id' => '3'], ['id' => '2'], ['id' => '1']
        ];

        $this->assertIsArray(A::key($arr, 'id'));

        $result = [
            '3' => ['id' => '3'],
            '2' => ['id' => '2'],
            '1' => ['id' => '1']
        ];
        $this->assertEquals($result, A::key($arr, 'id'));

        $result = [
            3 => ['id' => '3'],
            2 => ['id' => '2'],
            1 => ['id' => '1']
        ];
        $this->assertEquals($result, A::key($arr, 'id', 'intval'));
    }

    public function testFind() {
        $arr = [
            ['id' => '3'], ['id' => '2'], ['id' => '1', 'nested' => ['id' => 'test']]
        ];

        $result = ['id' => '2'];
        $test = A::find($arr, function($v) {
            return $v['id'] === '2';
        });
        $this->assertEquals($result, $test);
    }

    public function testFindBy() {
        $arr = [
            ['id' => '3'], ['id' => '2'],
            ['id' => '1', 'nested' => ['id' => 'test']]
        ];

        $result = ['id' => '2'];
        $this->assertEquals($result, A::findBy($arr, 'id', '2'));

        $result = ['id' => '1', 'nested' => ['id' => 'test']];
        $this->assertEquals($result, A::findBy($arr, ['nested', 'id'], 'test'));
    }

}
