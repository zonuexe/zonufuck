<?php

namespace zonuexe\Zonufuck\functions;

use function zonuexe\Zonufuck\exec_iseq;
use function zonuexe\Zonufuck\stream_for;

/**
 * Test case for function zonuexe\Zonufuck\exec_iseq()
 *
 * @copyright 2019 zonuexe
 * @author USAMI Kenta <tadsan@zonu.me>
 * @license GPL-3.0-or-later
 */
class ExecIseqTest extends \zonuexe\Zonufuck\TestCase
{
    /**
     * @dataProvider for_test
     */
    public function test(array $expected, array $iseq, string $input)
    {
        $output_resource = stream_for('', 'rw');
        $input_resource = stream_for($input, 'rw');

        exec_iseq($iseq, $input_resource, $output_resource, $actual_memory);

        $this->assertEquals($expected['output'], \stream_get_contents($output_resource));
        $this->assertEquals($expected['memory'], $actual_memory);
    }

    public function for_test()
    {
        return [
            [
                [
                    'output' => '',
                    'memory' => [0],
                ],
                [],
                ''
            ],
            [
                [
                    'output' => '',
                    'memory' => [1],

                ],
                [
                    ['inc', 1],
                ],
                '',
            ],
            [
                [
                    'output' => '',
                    'memory' => [2, 1],

                ],
                [
                    ['inc', 1],
                    ['inc', 1],
                    ['nxt'],
                    ['inc', 1],
                ],
                '',
            ],
            [
                [
                    'output' => '',
                    'memory' => [2, 1],

                ],
                [
                    ['inc', 1],
                    ['inc', 1],
                    ['nxt'],
                    ['inc', 1],
                ],
                '',
            ],
            [
                [
                    'output' => '',
                    'memory' => [-1],
                ],
                [
                    ['opn'],
                    ['cls'],
                    ['inc', 1],
                    ['dec', 1],
                    ['dec', 1],
                ],
                '',
            ],
        ];
    }

    /**
     * @dataProvider for_test_raise_exception
     */
    public function test_raise_exception(array $expected, array $iseq, string $input)
    {
        $this->expectException($expected['exception']);
        $this->expectExceptionMessage($expected['message']);

        $output_resource = stream_for('', 'rw');
        $input_resource = stream_for($input, 'rw');

        exec_iseq($iseq, $input_resource, $output_resource, $actual_memory);
    }

    public function for_test_raise_exception()
    {
        $unbalanced = [
            'exception' => \RuntimeException::class,
            'message' => 'Given unbalanced parenthesis',
        ];

        return [
            [
                $unbalanced,
                [
                    ['opn'],
                ],
                '',
            ],
            [
                $unbalanced,
                [
                    ['cls'],
                ],
                '',
            ],
            [
                $unbalanced,
                [
                    ['cls'],
                    ['opn'],
                ],
                '',
            ],
        ];
    }
}
