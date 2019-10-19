<?php

namespace zonuexe\Zonufuck\functions;

use function zonuexe\Zonufuck\strip_line_comment;

/**
 * Test case for function zonuexe\Zonufuck\strip_line_comment()
 *
 * @copyright 2019 zonuexe
 * @author USAMI Kenta <tadsan@zonu.me>
 * @license GPL-3.0-or-later
 */
class StripLineCommentTest extends \zonuexe\Zonufuck\TestCase
{
    /**
     * @dataProvider for_test
     */
    public function test(string $expected, string $code)
    {
        $this->assertEquals($expected, strip_line_comment('#', $code));
    }

    public function for_test()
    {
        return [
            ['', ''],
            ['', '#'],
            ['', '#a'],
            ['bbb', 'bbb#a'],
            [
                '
',
                '
#'
            ],
            [
                '
aaa',
                '###
aaa'
            ],
        ];
    }
}
