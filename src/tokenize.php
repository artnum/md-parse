<?php

declare(strict_types=1);

namespace md2ooxml;

use PhpOffice\PhpWord\Element\Section;
use md2ooxml\Token;

function tokenize (string $text): array 
{
    $tokens = [];
    $tokenIndex = 0;
    $eol = false;

    foreach (mb_str_split($text) as $char) {
        switch ($char) {
            case 0:
                $char = '�';
                break;
            case '°':
                $tokens[$tokenIndex] = ['type' => Token::DEGREE, 'value' => $char, 'count' => 1];
                break;
            case ' ':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::WS) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::WS, 'value' => $char, 'count' => 1 ];
                break;
            case "\t":
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::TAB) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::TAB, 'value' => $char, 'count' => 1];
                break;

            /* CR LN, CR or LN : if CR is encountered, it's a new line. $eol is 
             * set to true to skip the next character if it's a LN.
             * If LN is encountered, same as CR ... in case of LN CR (which is
             * unlikely).
             */
            case "\n":
                if ($eol) {
                    $eol = false;
                    continue 2;
                }
                $eol = true;
                $tokens[$tokenIndex] = ['type' => Token::LN, 'value' => $char, 'count' => 1];
                break;
            case "\r":
                if ($eol) {
                    $eol = false;
                    continue 2;
                }
                $eol = true;
                $tokens[$tokenIndex] = ['type' => Token::LN, 'value' => $char, 'count' => 1];
                break;

            case '#':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::HASH) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::HASH, 'value' => $char, 'count' => 1];
                break;
            case '*':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::ASTERISK) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::ASTERISK, 'value' => $char, 'count' => 1];
                break;
            case '_':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::UNDERSCORE) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::UNDERSCORE, 'value' => $char, 'count' => 1];
                break;
            case '[':
                $tokens[$tokenIndex] = ['type' => Token::LBRACKET, 'value' => $char, 'count' => 1];
                break;
            case ']':
                $tokens[$tokenIndex] = ['type' => Token::RBRACKET, 'value' => $char, 'count' => 1];
                break;
            case '(':
                $tokens[$tokenIndex] = ['type' => Token::LPAREN, 'value' => $char, 'count' => 1];
                break;
            case ')':
                $tokens[$tokenIndex] = ['type' => Token::RPAREN, 'value' => $char, 'count' => 1];
                break;
            case '<':
                $tokens[$tokenIndex] = ['type' => Token::LT, 'value' => $char, 'count' => 1];
                break;
            case '>':
                $tokens[$tokenIndex] = ['type' => Token::GT, 'value' => $char, 'count' => 1];
                break;
            case '0':
            case '1':
            case '2':
            case '3':
            case '4':
            case '5':
            case '6':
            case '7':
            case '8':
            case '9':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::DIGIT) {
                    $tokens[$tokenIndex - 1]['value'] .= $char;
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::DIGIT, 'value' => $char, 'count' => 1];
                break;
            case '-':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::DASH) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::DASH, 'value' => $char, 'count' => 1];
                break;
            case '.':
                $tokens[$tokenIndex] = ['type' => Token::DOT, 'value' => $char, 'count' => 1];
                break;
            case '`':
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::BACKTICK) {
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::BACKTICK, 'value' => $char, 'count' => 1];
                break;
            case '~':
                $tokens[$tokenIndex] = ['type' => Token::TILDE, 'value' => $char, 'count' => 1];
                break;
            case ':':
                $tokens[$tokenIndex] = ['type' => Token::COLON, 'value' => $char, 'count' => 1];
                break;
            case ';':
                $tokens[$tokenIndex] = ['type' => Token::SEMICOLON, 'value' => $char, 'count' => 1];
                break;
            case '"':
                $tokens[$tokenIndex] = ['type' => Token::DQUOTE, 'value' => $char, 'count' => 1];
                break;
            case "'":
                $tokens[$tokenIndex] = ['type' => Token::SQUOTE, 'value' => $char, 'count' => 1];
                break;
            case '\\':
                $tokens[$tokenIndex] = ['type' => Token::BACKSLASH, 'value' => $char, 'count' => 1];
                break;
            case '/':
                $tokens[$tokenIndex] = ['type' => Token::SLASH, 'value' => $char, 'count' => 1];
                break;
            case '@':
                $tokens[$tokenIndex] = ['type' => Token::AT, 'value' => $char, 'count' => 1];
                break;
            case '&':
                $tokens[$tokenIndex] = ['type' => Token::AMPERSAND, 'value' => $char, 'count' => 1];
                break;
            case '$':
                $tokens[$tokenIndex] = ['type' => Token::DOLLAR, 'value' => $char, 'count' => 1];
                break;
            case '%':
                $tokens[$tokenIndex] = ['type' => Token::PERCENT, 'value' => $char, 'count' => 1];
                break;
            case '^':
                $tokens[$tokenIndex] = ['type' => Token::CARET, 'value' => $char, 'count' => 1];
                break;
            case '+':
                $tokens[$tokenIndex] = ['type' => Token::PLUS, 'value' => $char, 'count' => 1];
                break;
            case '=':
                $tokens[$tokenIndex] = ['type' => Token::EQUAL, 'value' => $char, 'count' => 1];
                break;
            case '?':
                $tokens[$tokenIndex] = ['type' => Token::QUESTION, 'value' => $char, 'count' => 1];
                break;
            case '!':
                $tokens[$tokenIndex] = ['type' => Token::EXCLAMATION, 'value' => $char, 'count' => 1];
                break;
            case ',':
                $tokens[$tokenIndex] = ['type' => Token::COMMA, 'value' => $char, 'count' => 1];
                break;
            case '|':
                $tokens[$tokenIndex] = ['type' => Token::PIPE, 'value' => $char, 'count' => 1];
                break;
            case '+':
                $tokens[$tokenIndex] = ['type' => Token::PLUS, 'value' => $char, 'count' => 1];
                break;
            default:
                if ($tokenIndex > 0 && $tokens[$tokenIndex - 1]['type'] === Token::TEXT) {
                    $tokens[$tokenIndex - 1]['value'] .= $char;
                    $tokens[$tokenIndex - 1]['count']++;
                    continue 2;
                }
                $tokens[$tokenIndex] = ['type' => Token::TEXT, 'value' => $char, 'count' => 1];
                break;
        }
        $eol = false;
        $tokenIndex++;
    }
    return $tokens;
}


function si_unit_convert (array $tokens) 
{
    $out = [];
    $j = 0;

    while (($token = array_shift($tokens)) !== null) {
        /* a text token preceded with a digit or a space is considered a si unit 
         * if it matches a known si unit.
         */
        if (
                $token['type'] === 'text' 
                && (
                    (count($tokens) > 0 && $tokens[0]['type'] === 'digit')
                    || (count($tokens) > 1 && $tokens[1]['type'] === 'text')
                )
                && (
                    $out[$j - 1]['type'] === 'digit'
                    || $out[$j - 1]['type'] === 'ws'
                )
            )
        {
            /* *** units of length *** */
            if (count($tokens) > 0
                && (
                    $token['value'] === 'm'
                    || $token['value'] === 'cm'
                    || $token['value'] === 'mm'
                    || $token['value'] === 'km'
                )
                && (
                    $tokens[0]['value'] === '2' 
                    || $tokens[0]['value'] === '3'
                )
            ) {
                $number = array_shift($tokens)['value'];
                switch($number) {
                    case '2':
                        $out[] = [
                                'type' => 'si_unit',
                                'value' => match($token['value']) {
                                    'm' => 'm²',
                                    'cm' => 'cm²',
                                    'mm' => 'mm²',
                                    'km' => 'km²'
                                }
                            ];
                        $j++;
                        continue 2;
                    case '3':
                        $out[] = [
                                'type' => 'si_unit',
                                'value' => match($token['value']) {
                                    'm' => 'm³',
                                    'cm' => 'cm³',
                                    'mm' => 'mm³',
                                    'km' => 'km³',
                                }
                            ];
                        $j++;
                        continue 2;
                }
            }
        
            /* *** units of speed *** */
            if (
                count($tokens) > 1
                && (
                    $token['value'] === 'm'
                    || $token['value'] === 'km'
                )
                && $tokens[0]['type'] === 'slash'
                && (
                    $tokens[1]['value'] === 's'
                    || $tokens[1]['value'] === 'h'
                )
            ) {
                array_shift($tokens);
                $time = array_shift($tokens);
                $out[] = [
                        'type' => 'si_unit',
                        'value' => match($token['value']) {
                            'm' => match($time['value']) {
                                's' => 'm/s',
                                'h' => 'm/h'
                            },
                            'km' => match($time['value']) {
                                's' => 'km/s',
                                'h' => 'km/h'
                            }
                        }
                    ];
                $j++;
                continue;
            }
        }
        
        if (
            $token['type'] === 'degree'
            && (
                $out[$j - 1]['type'] === 'digit'
                || $out[$j - 1]['type'] === 'ws'
            )
        ) {
            if (count($tokens) > 0 && $tokens[0]['type'] === 'text') {
                switch ($tokens[0]['value']) {
                    case 'C':
                        array_shift($tokens);
                        $out[] = ['type' => 'si_unit', 'value' => '°C'];
                        $j++;
                        continue 2;
                    case 'F':
                        array_shift($tokens);
                        $out[] = ['type' => 'si_unit', 'value' => '°F'];
                        $j++;
                        continue 2;
                    case 'K':
                        array_shift($tokens);
                        $out[] = ['type' => 'si_unit', 'value' => 'K'];
                        $j++;
                        continue 2;
                }
            }

            $out[] = ['type' => 'si_unit', 'value' => '°'];
            $j++;
            continue;
        }


        $out[] = $token;
        $j++;
    }

    return $out;
}


