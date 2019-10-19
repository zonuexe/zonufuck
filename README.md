# Zonufuck

A Brainfuck interpreter written in PHP.

## Example

```php
<?php

use function zonuexe\Zonufuck\gen;

$BF = gen([
    'inc' => '+',
    'dec' => '-',
    'nxt' => '>',
    'prv' => '<',
    'opn' => '[',
    'cls' => ']',
    'put' => '.',
    'get' => ',',
]);


$input = fopen('php://memory', 'rw');
fwrite($input, 'Hello');
rewind($input);

$BF('>+++++++++[<++++++++>-]<.>+++++++[<++++>-]<+.+++++++..+++.[-]>++++++++[<++
++>-]<.>+++++++++++[<+++++>-]<.>++++++++[<+++>-]<.+++.------.--------.[-]>
++++++++[<++++>-]<+.[-]++++++++++.', $input, $output);

rewind($output);
echo stream_get_contents($output);
```
