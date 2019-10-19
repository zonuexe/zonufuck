<?php

namespace zonuexe\Zonufuck\functions;

use function zonuexe\Zonufuck\compile;

/**
 * Test case for function zonuexe\Zonufuck\compile()
 *
 * @copyright 2019 zonuexe
 * @author USAMI Kenta <tadsan@zonu.me>
 * @license GPL-3.0-or-later
 */
class CompileTest extends \zonuexe\Zonufuck\TestCase
{
    /**
     * @dataProvider for_test
     */
    public function test(array $expected, array $tokens, string $code)
    {
        $this->assertEquals($expected, compile($tokens, $code));
    }

    public function for_test()
    {
        $bf_tokens = [
            'inc' => '+',
            'dec' => '-',
            'nxt' => '>',
            'prv' => '<',
            'opn' => '[',
            'cls' => ']',
            'put' => '.',
            'get' => ',',
        ];

        return [
            [
                [],
                $bf_tokens,
                ''
            ],
            [
                [
                    ['inc', 1],
                ],
                $bf_tokens,
                '+',
            ],
        ];
    }
}
