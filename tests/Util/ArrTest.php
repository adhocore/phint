<?php

namespace Ahc\Phint\Test;

use Ahc\Phint\Util\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function arrProvider()
    {
        return [
            [[6, 7, 8], [2, 3, [4, 5]], [2, 3, [4, 5]]],
            [[2, 3, [4, 5]], [2, 3, [4, 5]], [2, 3, [4, 5]]],
            [['1', 2, 'a' => 3], ['2', 3, 'a' => 4], ['2', 3, 'a' => 4]],
            [['a' => 3, 'b' => [1, 2]], ['a' => [4], 'b' => [0]], ['a' => [4], 'b' => [0, 2]]],
        ];
    }

    /** @dataProvider arrProvider */
    public function testMergeRecursive($array1, $array2, $expectedArray)
    {
        $this->assertEquals($expectedArray, Arr::mergeRecursive($array1, $array2));
    }
}
