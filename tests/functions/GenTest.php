<?php

namespace zonuexe\Zonufuck\functions;

use function zonuexe\Zonufuck\gen;
use function zonuexe\Zonufuck\stream_for;
use function zonuexe\Zonufuck\strip_line_comment;

/**
 * Test case for function zonuexe\Zonufuck\gen()
 *
 * This test cases are based kinaba's brainfuck article that licensed under NYSL.
 *
 * @see http://www.kmonos.net/alang/etc/brainfuck.php
 * @copyright 2019 zonuexe
 * @author USAMI Kenta <tadsan@zonu.me>
 * @license GPL-3.0-or-later
 */
class GenTest extends \zonuexe\Zonufuck\TestCase
{
    private const BF_TOKENS = [
        'inc' => '+',
        'dec' => '-',
        'nxt' => '>',
        'prv' => '<',
        'opn' => '[',
        'cls' => ']',
        'put' => '.',
        'get' => ',',
    ];


    /**
     * @dataProvider for_test
     */
    public function test(string $expected, string $code, string $input = '')
    {
        $output_resource = stream_for('', 'rw');
        $input_resource = stream_for($input, 'rw');

        $code = strip_line_comment('#', $code);

        $bf = gen(self::BF_TOKENS);
        $bf($code, $input_resource, $output_resource);

        \rewind($output_resource);

        $this->assertEquals($expected, \stream_get_contents($output_resource));
    }

    public function for_test()
    {
        yield 'No.1' => [
            'hoge',
            '
++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++
++++++++++++++++++++++++++++++++
++++++++.
+++++++.
--------.
--.',
        ];

        yield 'No.2' => [
                'hoge',
                '++++++++++[>++++++++++<-]>++++.+++++++.--------.--.',
        ];

        yield 'No.3 Echo' => [
            'echo',
            '+[>,.<]',
            'echo',
        ];

        yield 'No.4 add' => [
            '3',
            '+>++><<>[-<+>]<++++++++++++++++++++++++++++++++++++++++++++++++.',
        ];

        yield 'No.5 mul' => [
            '8',
            '
++++>++><<
[-
  >[->>+<<]
  >>[-<+<+>>]
  <<<
]>>
++++++++++++++++++++++++++++++++++++++++++++++++.',
            '',
        ];

        yield 'No.6 condition' => [
                '2',
                '+++++     # {0} = ?
>+++<     # {1} = ?

>[                    # while( {1} ) do
  <[->>+>+<<<]>       #   {2:3} = !{0}
  >>[-<<<+>>>]<<      #   {0}   = !{3}
  >[[-]<<->>]<        #   if({2}) then decr {0}
  -                   #   decr {1}
]<                    # end

++++++++++++++++++++++++++++++++++++++++++++++++.',
        ];

        $code = '
### init: ##################################

>>++++++++[->++++++++<]>      # {3} = 0x40
>>>+++++++++[->++++++++++<]>  # {7} = 0x5A   ptr=7

### main: ##################################

[<<,                # {5} = getchar()

 ## 00 00 00 40 00 *ch 00 5A 00 00 00
  [->+<<+<<+>>>]    # {2:4:6} = !{5}
  <<<[->>>+<<<]>>>  # {5}     = !{2}
 ## 00 00 00 40 ch *ch ch 5A 00 00 00


  >>[->+>>+<<<]>    # {8:10}  = !{7}
 ## 00 00 00 40 ch ch ch 00 *5A 00 5A
  [                 # while( {8} ) do
    <<[->+>>+<<<]>> #   {7:9} = !{6}
    >[-<<<+>>>]<    #   {6}   = !{9}
    <[[-]<->]>      #   if({7}) then decr{6}
    -               #   decr {8}
  ]                 # end
  >>[-<<<+>>>]<<<<< # {7} = !{10}
 ## 00 00 00 40 ch *ch ans1 5A 00 00 00
 ##   ans1 = 0   iff   input le 5A


  <<[-<+<<+>>>]<    # {0:2}  = !{3}
 ## 40 00 *40 00 ch ch ans1 5A 00 00 00
  [                 # while( {2} ) do
    >>[-<+<<+>>>]<< #   {1:3} = !{4}
    <[->>>+<<<]>    #   {4}   = !{1}
    >[[-]>-<]<      #   if({3}) then decr{4}
    -               #   decr {2}
  ]                 # end
  <<[->>>+<<<]>>>>> # {3} = !{0}
 ## 00 00 00 40 ans2 *ch ans1 5A 00 00 00
 ##   ans2 = 0   iff   input le 40


 # if(ans2) if(not ans1) {5} add 0x20
  <[[-]                                  # if( {4} ) then do
    >++++++++++++++++++++++++++++++++    #   {5} add 0x20
    >[[-]                                #   if( !{6} ) then do
      <--------------------------------> #     {5} sub 0x20
    ]<<                                  #   end
  ]>>[-]<                                # end; !{6}

 ## 00 00 00 40 00 *ch 00 5A 00 00 00
.>>] # putchar {5}';

        $examples = [
            'aiueo' => 'aiueo',
            'HoGe' => 'hoge',
            'Afajfa;loZjlbzzAaB' => 'afajfa;lozjlbzzaab',
        ];

        foreach ($examples as $input => $expected) {
            yield "No.7 {$input}" => [$expected, $code, $input];
        }
    }
}
