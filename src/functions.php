<?php

declare(strict_types=1);

/**
 * A Brainfuck interpreter written in PHP by @zonuexe
 *
 * @copyright 2019 zonuexe
 * @author USAMI Kenta <tadsan@zonu.me>
 * @license GPL-3.0-or-later
 */
namespace zonuexe\Zonufuck
{
    function compile(array $tokens, string $code): array
    {
        $flipped = \array_flip($tokens);

        if (\count($tokens) !== \count($flipped)) {
            throw new \LogicException('Given duplicated token');
        }

        $pattern = '/' . \implode('|', [
             \preg_quote($tokens['inc'], '/'),
             \preg_quote($tokens['dec'], '/'),
             \preg_quote($tokens['nxt'], '/'),
             \preg_quote($tokens['prv'], '/'),
             \preg_quote($tokens['opn'], '/'),
             \preg_quote($tokens['cls'], '/'),
             \preg_quote($tokens['put'], '/'),
             \preg_quote($tokens['get'], '/'),
        ]) . '/';

        if (\preg_match_all($pattern, $code, $matches) === 0) {
            return [];
        }

        $code_tokens = $matches[0];

        $instructions = [
            'inc' => ['inc', 1],
            'dec' => ['dec', 1],
        ];

        return \array_map(function (string $t) use ($flipped, $instructions) {
            $name = $flipped[$t];

            return $instructions[$name] ?? [$name];
        }, $code_tokens);
    }

    /**
     * @param array<int,array{0:string,1?:int}> $iseq
     * @param resource $input
     * @param resource $output
     */
    function exec_iseq(array $iseq, $input, $output, array &$memory = null): void
    {
        $i = 0;
        $p = 0;
        /** @var int[] */
        $memory = [0];

        while (true) {
            if (!isset($iseq[$i])) {
                return;
            }

            $instruction = $iseq[$i][0];
            $operand = $iseq[$i][1] ?? null;

            if (!isset($memory[$p])) {
                $memory[$p] = 0;
            }

            switch ($instruction) {
                case 'inc':
                    $memory[$p]++;
                    $i++;
                    break;
                case 'dec':
                    $memory[$p]--;
                    $i++;
                    break;
                case 'nxt':
                    $p++;
                    $i++;
                    break;
                case 'prv':
                    $p--;
                    $i++;
                    break;
                case 'put':
                    \fwrite($output, \chr($memory[$p]));
                    $i++;
                    break;
                case 'get':
                    $input_byte = \fread($input, 1);
                    if ($input_byte === false || \strlen($input_byte) === 0) {
                        throw new \RuntimeException('Failed read byte from $input');
                    }

                    $memory[$p] = \ord($input_byte);
                    $i++;
                    break;
                case 'opn':
                    if ($memory[$p] === 0) {
                        $i = $operand ?? pos_close($iseq, $i);
                    }

                    $i++;

                    break;
                case 'cls':
                    $i = $operand ?? pos_open($iseq, $i);
                    break;
                default:
                    throw new \LogicException("Unexpected instruction: {$instruction}");
            }
        }
    }

    /**
     * @param string[] $tokens
     */
    function gen(array $tokens): \Closure
    {
        /**
         * @param resource $input
         * @param resource $output
         */
        return function (string $code, $input, $output) use ($tokens): ?\RuntimeException {
            $bf = compile($tokens, $code);
            try {
                exec_iseq($bf, $input, $output);
            } catch (\RuntimeException $e) {
                return $e;
            }

            return null;
        };
    }

    /**
     * @param array<int,array{0:string,1?:int}> $iseq
     * @param int $i
     * @return int
     */
    function pos_close(array $iseq, int $i): int
    {
        $nest = 0;

        while (true) {
            $i++;

            if (!isset($iseq[$i])) {
                throw new \RuntimeException('Given unbalanced parenthesis');
            }

            if ($nest < 0) {
                throw new \LogicException('Damepo');
            }

            $instruction = $iseq[$i][0];

            if ($instruction === 'cls') {
                if ($nest === 0) {
                    break;
                }

                $nest--;
            } elseif ($instruction === 'opn') {
                $nest++;
            }
        }

        return $i;
    }

    /**
     * @param array<int,array{0:string,1?:int}> $iseq
     * @param int $i
     * @return int
     */
    function pos_open(array $iseq, int $i): int
    {
        $nest = 0;

        while (true) {
            $i--;

            if (!isset($iseq[$i])) {
                throw new \RuntimeException('Given unbalanced parenthesis');
            }

            if ($nest < 0) {
                throw new \LogicException('Damepo');
            }

            $instruction = $iseq[$i][0];

            if ($instruction === 'opn') {
                if ($nest === 0) {
                    break;
                }

                $nest--;
            } elseif ($instruction === 'cls') {
                $nest++;
            }
        }

        return $i;
    }

    /**
     * @return resource
     */
    function stream_for(string $content, string $mode)
    {
        $fp = \fopen('php://memory', $mode);

        if ($fp === false) {
            throw new \LogicException('Failed open resource');
        }

        \fwrite($fp, $content);
        \rewind($fp);

        return $fp;
    }

    function strip_line_comment(string $comment_symbol, string $code): string
    {
        return \preg_replace('/' . \preg_quote($comment_symbol) . '.*$/m', '', $code);
    }
}
