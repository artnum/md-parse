<?php
declare(strict_types=1);

/*
 * GFM current support state :
 * 
 * x -> done
 * ~ -> in progress
 * # -> not planned
 * 
 * 
 * - [x] ATX Header
 * - [#] Setext Header
 * - [~] Code block
 * - [ ] Fenced code block
 * - [x] Blockquote
 * - [x] List
 * - [x] Table
 * - [ ] Inline code
 * - [~] Inline strong
 * - [+] Inline emphasis
 * - [+] Inline link
 * - [ ] Inline image
 * - [ ] Inline autolink
 * - [#] Inline HTML
 * - [ ] Strikethrough
 * 
 * below is mostly AI generated stuff, so it's not really useful
 * - [#] Task list
 * - [x] Emoji (support unicode, so shouldn't be a problem)
 * - [ ] Footnote
 * - [ ] Definition list
 * - [ ] Abbreviation
 * - [ ] Custom container
 * - [ ] Custom block
 * - [ ] Custom inline
 * - [ ] Front matter
 * - [ ] Math
 * - [ ] Mermaid
 * - [ ] Flowchart
 * - [ ] Sequence diagram
 * - [ ] Gantt diagram
 * - [ ] PlantUML
 * - [ ] Chart
 * - [ ] Presentation
 * - [ ] Diagram
 * - [ ] Table of content
 * - [ ] TOC
 * - [ ] Anchor
 * - [ ] Mark
 * - [ ] Superscript
 * - [ ] Subscript
 * - [ ] Ins
 * - [ ] Del
 * - [ ] Ruby
 * - [ ] KaTeX
 * - [ ] Mermaid
 * - [ ] Flowchart
 * - [ ] Sequence diagram
 * - [ ] Gantt diagram
 * - [ ] PlantUML
 * - [ ] Chart
 * - [ ] Presentation
 * - ... ok AI, stop adding stuff, you are repeating yourself and inventing stuff
 */

namespace md2ooxml;

use md2ooxml\Token;
use phpDocumentor\Reflection\DocBlock\Tag;

enum BlockState {
    case TEXT;
    case HEADER;
    case CODE;
    case QUOTE;
    case LIST;
    case TABLE;
}

enum InlineState {
    case EMPHASIS;
    case STRONG;
    case CODE;
    case LINK;
    case IMAGE;
}

enum TagType {
    case NONE;
    case EMPTYLINE;
    case STRONG;
    case EMPHASIS;
    case HEADER;
    case PARAGRAPH;
    case PRE;
    case QUOTE;
    case TEXT;
    case UL;
    case OL;
    case LI;
    case LINEBREAK;
    case URLCAPTION;
    case URL;
    case REFERENCE;
    case REFERENCEURL;
    case CODE;
    case TABLE;
    case TABLEHEADER;
    case TABLEROW;
    case TABLECELL;
    case STRIKETHROUGH;

}

function token2string(Token $token): string
{
    return match ($token) {
        Token::NONE =>'NONE',
        Token::LN =>'LN',
        Token::WS =>'WS',
        Token::TAB =>'TAB',
        Token::HASH =>'HASH',
        Token::ASTERISK =>'ASTERISK',
        Token::UNDERSCORE =>'UNDERSCORE',
        Token::LBRACKET =>'LBRACKET',
        Token::RBRACKET =>'RBRACKET',
        Token::LPAREN =>'LPAREN',
        Token::RPAREN =>'RPAREN',
        Token::EXCLAMATION =>'EXCLAMATION',
        Token::LT =>'LT',
        Token::GT =>'GT',
        Token::DIGIT =>'DIGIT',
        Token::DASH =>'DASH',
        Token::DOT =>'DOT',
        Token::BACKTICK =>'BACKTICK',
        Token::TILDE =>'TILDE',
        Token::PIPE =>'PIPE',
        Token::COLON =>'COLON',
        Token::SEMICOLON =>'SEMICOLON',
        Token::DQUOTE =>'DQUOTE',
        Token::QUOTE =>'QUOTE',
        Token::SQUOTE =>'SQUOTE',
        Token::BACKSLASH =>'BACKSLASH',
        Token::SLASH =>'SLASH',
        Token::AT =>'AT',
        Token::AMPERSAND =>'AMPERSAND',
        Token::LETTER =>'LETTER',
        Token::DEGREE =>'DEGREE',
        Token::CR =>'CR',
        Token::PERCENT =>'PERCENT',
        Token::TEXT =>'TEXT',
        Token::PLUS =>'PLUS',
        Token::EQUAL =>'EQUAL',
        Token::QUESTION =>'QUESTION',
        Token::COMMA =>'COMMA',
        Token::MINUS =>'MINUS',
        Token::CARET =>'CARET',
        Token::DOLLAR =>'DOLLAR',
    };
}

function tag2text(TagType $tag): string {
    switch ($tag) {
        case TagType::EMPTYLINE:
            return 'EMPTYLINE';
        case TagType::OL:
            return 'OL';
        case TagType::UL:
            return 'UL';
        case TagType::LINEBREAK:
            return 'LINEBREAK';
        case TagType::STRONG:
            return 'STRONG';
        case TagType::EMPHASIS:
            return 'EMPHASIS';
        case TagType::HEADER:
            return 'HEADER';
        case TagType::PARAGRAPH:
            return 'PARAGRAPH';
        case TagType::PRE:
            return 'PRE';
        case TagType::QUOTE:
            return 'QUOTE';
        case TagType::TEXT:
            return 'TEXT';
        case TagType::LI:
            return 'LI';
        case TagType::NONE:
            return 'NONE';
        case TagType::URL:
            return 'URL';
        case TagType::URLCAPTION:
            return 'URLCAPTION';
        case TagType::REFERENCE:
            return 'REFERENCE';
        case TagType::TABLE:
            return 'TABLE';
        case TagType::TABLEHEADER:
            return 'TABLEHEADER';
        case TagType::TABLEROW:
            return 'TABLEROW';
        case TagType::TABLECELL:
            return 'TABLECELL';
        case TagType::STRIKETHROUGH:
            return 'STRIKETHROUGH';
        default:
            return 'UNKNOWN';
    }
}

class MDElement {
    public TagType $tag = TagType::NONE;
    public string $value = '';
    public int $level = 0;
    public bool $close = false;
    public bool $block = false;
    function __construct(TagType $tag = TagType::NONE, int $level = 0, bool $close = false, string $value = '') {
        $this->tag = $tag;
        $this->value = $value;
        $this->level = $level;
        $this->close = $close;

        switch($this->tag) {
            case TagType::PARAGRAPH:
            case TagType::CODE:
            case TagType::HEADER:
            case TagType::PRE:
            case TagType::QUOTE:
            case TagType::UL:
            case TagType::OL:
                $this->block = true;
                break;
        }
    }
}

/* check if we are at the beginning of a line, ignoring whitespaces */
function is_beginning_of_line (array $tokens, int $index): bool {
    if ($index === 0) {
        return true;
    }
    $beforeType = Token::TEXT;
    $offset = $index - 1;
    while ($offset >= 0) {
        $beforeType = $tokens[$offset]['type'];
        if ($beforeType !== Token::WS && $beforeType !== Token::TAB) {
            break;
        }
        $offset--;
    }
    return $beforeType === Token::LN;
}

function count_beginning_of_line_ws (array $tokens, int $index): int {
    $count = 0;
    $offset = $index - 1;
    while ($offset >= 0 && count($tokens) > $offset) {
        if ($tokens[$offset]['type'] !== Token::WS && $tokens[$offset]['type'] !== Token::TAB) {
            break;
        }
        $count += $tokens[$offset]['count'];
        $offset--;
    }
    return $count;
}

function peek_previous_token_type (array $tokens, int $index): Token {
    if ($index - 1 < 0) {
        return Token::NONE;
    }
    return $tokens[$index - 1]['type'];
}

function peek_next_token_type (array $tokens, int $index): Token {
    if ($index + 1 >= count($tokens)) {
        return TagType::NONE;
    }
    return $tokens[$index + 1]['type'];
}

function peek_at_next_non_ws_token_type (array $tokens, int $index): Token {
    $index++;
    for (   $tokenCount = count($tokens)
            ; $index < $tokenCount && ($tokens[$index]['type'] === Token::WS || $tokens[$index]['type'] === Token::TAB)
            ; $index++
);
    if ($index >= $tokenCount) {
        return Token::NONE;
    }
    return $tokens[$index]['type'];
}

function is_forward_tokens_list (array $tokens, int $index): bool {
    $tokenCount = count($tokens);
    for (; $index < $tokenCount && ($tokens[$index] === Token::WS || $tokens[$index] === Token::TAB); $index++);
    if ($tokens[$index] === Token::DASH) {
        return true;
    }
    if ($tokens[$index] === Token::DIGIT && peek_next_token_type($tokens, $index) === Token::DOT) {
        return true;
    }
    return false;
}

function is_tag_open (array &$opened, TagType $tag): bool {
    return count(array_filter($opened, fn($t) => $t['type'] === $tag)) > 0;
}

function set_tag_open (array &$opened, TagType $tag, mixed $data = null) {
    if (is_tag_open($opened, $tag)) {
        return $opened;
    }
    $opened[] = ['type' => $tag, 'count' => 1, 'data' => $data];
}

function get_open_tags_by_type (array $opened, TagType $type): array {
    return array_filter($opened, fn($t) => $t['type'] === $type);    
}

function unset_all_tags_open(array &$opened, TagType $tag) {
    $opened = array_filter($opened, fn($t) => $t['type'] !== $tag);
}


function peek_previous_element(array $elements, int $index): MDElement {
    if ($index - 1 < 0) {
        return new MDElement();
    }
    return $elements[$index - 1];
}

/**
 * Check if the previous element is a header
 * @param array $elements 
 * @param int $index 
 * @return int Return TABLEROW index if it's a delimiter row or -1 if not.
 */
function is_previous_tablerow_delimiter_row (array $elements, int $index): int {
    if ($index === 0) {
        return -1;
    }
    $offset = $index - 1;
    while ($offset >= 0 && $elements[$offset]->tag !== TagType::TABLEROW) {
        if ($elements[$offset]->tag === TagType::TEXT) {
            if (preg_match('/^[^\:\-\s]*$/', $elements[$offset]->value)) {
                return -1;
            }
        }
        $offset--;
    }

    return $offset;
}

function isEmptyValue($value)
{
    if (preg_match('/^[ \s\t\n\r\n]*$/', $value)) {
        return true;
    }
    return false;
}

function setLILevel($elements) 
{
    $openList = false;
    $levels = [];
    $from = 0;
    foreach($elements as $k => $element) {
        if (
                ($element->tag === TagType::UL || $element->tag === TagType::OL)
                && !$element->close
        ) {
            $openList = true;
            $from = $k;
            continue;
        }

        if (
                ($element->tag === TagType::UL || $element->tag === TagType::OL)
                && $element->close
        ) {
            $openList = false;
            sort($levels);

            for ($i = $from; $i < $k; $i++) {
                if (!isset($elements[$i])) { continue; } // admit there might be some hole in our array
                if ($elements[$i]->tag !== TagType::LI || $elements[$i]->close) { continue; }
                $elements[$i]->level = array_search($elements[$i]->level, $levels);
            }
            $levels = [];
            continue;
        }
        if ($openList && $element->tag === TagType::LI && !$element->close) {
            if (!in_array($element->level, $levels)) {
                $levels[] = $element->level;
            }
        }
    }

    return $elements;
}

/**
 * Giant parsing function, try to match every situtation possible within 
 * markdown syntax (try to be as close as possible to Github Flavored Markdown
 * but do not support all features and cases (like HTML tags as it is not easly
 * convertible to OOXML (well I am kinda lying here, but ... )).
 * It output a stream of open/close tags except self closing tags like LINEBREAK
 * or TEXT tags.
 * 
 * @param array $tokens An array of token produced by the tokenizer
 * @return array An array of MDElement
 *  
 */
function parse(array $tokens): array
{
    $result = [];
    $tokenCount = count($tokens);
    if ($tokenCount === 0) {
        return [];
    }
    $opened = [];

    $state = [
        'expectURL' => false,
        'tableStarted' => false
    ];

    for ($i = 0; $i < $tokenCount; $i++) {
        $token = $tokens[$i];

        /* Unordered list can be nested */
        if ($token['type'] === Token::DASH && is_beginning_of_line($tokens, $i)) {
            $wsBefore = count_beginning_of_line_ws($tokens, $i);
            if (!is_tag_open($opened, TagType::UL)) {
                $result[] = new MDElement(TagType::UL, $wsBefore, false);
                set_tag_open($opened, TagType::UL, $wsBefore);
            }
            $result[] = new MDElement(TagType::LI, count_beginning_of_line_ws($tokens, $i));
            set_tag_open($opened, TagType::LI);
            continue;
        }

        if ($token['type'] === Token::DIGIT && is_beginning_of_line($tokens, $i)) {
            if (peek_next_token_type($tokens, $i) === Token::DOT) {
                /* close previous list item */
                if (is_tag_open($opened, TagType::LI)) {
                    $result[] = new MDElement(TagType::LI, 0, true);
                    unset_all_tags_open($opened, TagType::LI);
                }
                if (!is_tag_open($opened, TagType::OL)) {
                    $result[] = new MDElement(TagType::OL);
                    set_tag_open($opened, TagType::OL);
                }
       
                $result[] = new MDElement(TagType::LI, count_beginning_of_line_ws($tokens, $i));
                set_tag_open($opened, TagType::LI);
        
                $i++; // skip the dot token
                continue;
            }
        }

        /* do we start a table ? */
        if (
            $token['type'] === Token::PIPE
            && !is_tag_open($opened, TagType::TABLE)
            && is_beginning_of_line($tokens, $i)
        ) {
            /* we open a table here */
            $result[] = new MDElement(TagType::TABLE);
            set_tag_open($opened, TagType::TABLE);

            $result[] = new MDElement(TagType::TABLEROW);
            set_tag_open($opened, TagType::TABLEROW);

            $result[] = new MDElement(TagType::TABLECELL);
            set_tag_open($opened, TagType::TABLECELL);
            continue;
        }

        if (
            $token['type'] === Token::PIPE
            && is_tag_open($opened, TagType::TABLE)
        ) {
            if (is_tag_open($opened, TagType::TABLECELL)) {
                $result[] = new MDElement(TagType::TABLECELL, 0, true);
                unset_all_tags_open($opened, TagType::TABLECELL);
            }
         
            if (peek_at_next_non_ws_token_type($tokens, $i) !== Token::LN) {
                $result[] = new MDElement(TagType::TABLECELL);
                set_tag_open($opened, TagType::TABLECELL);
            }
            continue;
        }

        /* ATX header, forces compliance with a hash followed by a space */
        if (
                $token['type'] === Token::HASH
                && $token['count'] <= 6 
                && is_beginning_of_line($tokens, $i)
                && peek_next_token_type($tokens, $i) === Token::WS
        ) {
            $result[] = new MDElement(TagType::HEADER, $token['count']);
            set_tag_open($opened, TagType::HEADER, $token['count']);
            /* trim whitespaces after the header token */
            while ($i + 1 < $tokenCount && $tokens[$i + 1]['type'] === Token::WS) {
                $i++;
            }
            continue;
        }

        if (!is_tag_open($opened, TagType::PRE)) {
            /* emphasis and strong, not in preformatted block */
            if ($token['type'] === Token::ASTERISK || $token['type'] === Token::UNDERSCORE) {
                $type = TagType::EMPHASIS;

                /* save the token just before */
                $beforeType = Token::TEXT;
                if ($i - 1 >= 0) { $beforeType = $tokens[$i - 1]['type']; }

                /* if it reapeat, it's strong */
                if ($i + 1 < $tokenCount && $token['type'] === $tokens[$i + 1]['type']) {
                    $i++;
                    $type = TagType::STRONG;
                }

                if (
                        is_tag_open($opened, $type)
                        && $beforeType !== Token::WS 
                        && $beforeType !== Token::TAB
                ) {
                    $result[] = new MDElement($type, 0, true);
                    unset_all_tags_open($opened, $type);
                    continue;
                } else {
                    if (
                            $i + 1 < $tokenCount 
                            && $tokens[$i + 1]['type'] !== Token::WS
                            && $tokens[$i + 1]['type'] !== Token::TAB
                    ) {
                        $result[] = new MDElement($type);
                        set_tag_open($opened, $type);
                        continue;
                    }
                }            
            }
        }

        /* quote block, starts with > */
        if ($token['type'] === Token::GT) {
            if (is_beginning_of_line($tokens, $i)) {
                /* if we are already in a quote block, just add a line break */
                if (count($result) > 0 && $result[count($result) - 1]->tag === TagType::QUOTE) {
                    $result[count($result) - 1] = new MDElement(TagType::LINEBREAK);
                    set_tag_open($opened, TagType::QUOTE);
                    continue;
                }

                $result[]  = new MDElement(TagType::QUOTE);
                set_tag_open($opened, TagType::QUOTE);
                continue;
            }
        }

        /* preformatted block, starts with 4 spaces but avoid list */
        if (
                $token['type'] === Token::WS 
                && $token['count'] >= 4
                && !is_forward_tokens_list($tokens, $i)
                && !is_tag_open($opened, TagType::UL) 
                && !is_tag_open($opened, TagType::OL)
        ) {
            if (is_beginning_of_line($tokens, $i)) {
                /* if we are already in a pre block, just add a line break */
                if (count($result) > 0 && $result[count($result) - 1]->tag === TagType::PRE) {
                    $result[count($result) - 1] = new MDElement(TagType::LINEBREAK);
                    set_tag_open($opened, TagType::PRE);
                    if ($token['count'] > 4) {
                        $result[] = new MDElement(TagType::TEXT, 0, false, str_repeat(' ', $token['count'] - 4));
                    }
                    continue;
                }

                $result[] = new MDElement(TagType::PRE);
                set_tag_open($opened, TagType::PRE);
                if ($token['count'] > 4) {
                    $result[] = new MDElement(TagType::TEXT, 0, false, str_repeat(' ', $token['count'] - 4));
                }
                continue;
            }
        }
       
        if ($token['type'] === Token::LN) {
            /* header closed by new line */
            if (is_tag_open($opened, TagType::HEADER)) {
                $openedTags = get_open_tags_by_type($opened, TagType::HEADER);
                $openedTag = array_shift($openedTags);
                $result[] = new MDElement(TagType::HEADER, $openedTag['data'], true);
                unset_all_tags_open($opened, TagType::HEADER);
                continue;
            }
            /* preformatted text closed by new line */
            if (is_tag_open($opened, TagType::PRE)) {
                $result[] = new MDElement(TagType::PRE, 0, true);
                unset_all_tags_open($opened, TagType::PRE);
                continue;
            }
            /* quote closed by new line */
            if (is_tag_open($opened, TagType::QUOTE)) {
                $result[] = new MDElement(TagType::QUOTE, 0, true);
                unset_all_tags_open($opened, TagType::QUOTE);
                continue;
            }
            /* list closed by new line */
            if (is_tag_open($opened, TagType::LI)) {
                $result[] = new MDElement(TagType::LI, 0, true);
                unset_all_tags_open($opened, TagType::LI);
                continue;
            }

            if (is_tag_open($opened, TagType::UL)) {
                $result[] = new MDElement(TagType::UL, 0, true);
                unset_all_tags_open($opened, TagType::UL);
                continue;
            }

            if (is_tag_open($opened, TagType::OL)) {
                $mdElement = new MDElement();
                $mdElement->tag = TagType::OL;
                $mdElement->close = true;
                $result[] = $mdElement;
                unset_all_tags_open($opened, TagType::OL);
                continue;
            }

            /* Close table */
            if (
                is_tag_open($opened, TagType::TABLE)
                && peek_at_next_non_ws_token_type($tokens, $i) !== Token::PIPE
            ) {
                if (is_tag_open($opened, TagType::TABLECELL)) {
                    $result[] = new MDElement(TagType::TABLECELL, 0, true);
                    unset_all_tags_open($opened, TagType::TABLECELL);
                    continue;
                }

                if (is_tag_open($opened, TagType::TABLEROW)) {
                    $result[] = new MDElement(TagType::TABLEROW, 0, true);
                    unset_all_tags_open($opened, TagType::TABLEROW);
                    continue;
                }

                if (is_tag_open($opened, TagType::TABLEHEADER)) {
                    $result[] = new MDElement(TagType::TABLEHEADER, 0, true);
                    unset_all_tags_open($opened, TagType::TABLEHEADER);
                    continue;
                }
    
                $result[] = new MDElement(TagType::TABLE, 0, true);
                unset_all_tags_open($opened, TagType::TABLE);
                continue;
            }

            /* close table row and reopen because next char is a pipe and, so,
             * the table continue
             */
            if (
                is_tag_open($opened, TagType::TABLE)
                && peek_at_next_non_ws_token_type($tokens, $i) === Token::PIPE
            ) {
                if (is_previous_tablerow_delimiter_row($result, count($result) - 1) !== -1) {
                    do {
                        $current = array_pop($result);
                    } while(
                        $current->tag !== TagType::TABLEROW 
                        && count($result) > 0
                    );

                    for ($k = count($result) - 1; $k >= 0; $k--) {
                        if ($result[$k]->tag === TagType::TABLEROW) {
                            $result[$k]->tag = TagType::TABLEHEADER;
                            if (!$result[$k]->close) { break;}
                        }
                    }
                    unset_all_tags_open($opened, TagType::TABLEROW);
                }

                if (is_tag_open($opened, TagType::TABLECELL)) {
                    $result[] = new MDElement(TagType::TABLECELL, 0, true);
                }

                if (is_tag_open($opened, TagType::TABLEROW)) {
                    $result[] = new MDElement(TagType::TABLEROW, 0, true);
                }

                if (is_tag_open($opened, TagType::TABLEHEADER)) {
                    $result[] = new MDElement(TagType::TABLEHEADER, 0, true);
                    unset_all_tags_open($opened, TagType::TABLEHEADER);
                }

                $result[] = new MDElement(TagType::TABLEROW);
                set_tag_open($opened, TagType::TABLEROW);
                $result[] = new MDElement(TagType::TABLECELL);
                set_tag_open($opened, TagType::TABLECELL);

                $i++;
                continue;

            }

            $previousTag = peek_previous_element($result, count($result))->tag;

            if(
                    $previousTag === TagType::LI
                    || $previousTag === TagType::OL
                    || $previousTag === TagType::UL
            ) {
                continue;
            }

            /* two line breaks in a row is an empty line */
            if ($previousTag  === TagType::LINEBREAK) {
                $result[count($result) - 1]->tag = TagType::EMPTYLINE;
                continue;
            }
            /* if there is an empty line, don't produce anymore linebrak */
            if ($previousTag  === TagType::EMPTYLINE) {
                continue;
            }
            /* this is a line break, output it */
            $result[] = new MDElement(TagType::LINEBREAK);
            continue;
        } /* end of LN */

        /* *** most inline element *** */

        /* handle reference */
        if ($token['type'] === Token::LBRACKET && $state['expectURL']) {

        }

        /* handle url */
        if ($token['type'] === Token::LBRACKET) {
            $result[] = new MDElement(TagType::URLCAPTION, 0, false, $token['value']);
            set_tag_open($opened, TagType::URLCAPTION);
            continue;
        }
        if ($token['type'] === Token::RBRACKET && is_tag_open($opened, TagType::URLCAPTION)) {
            $result[] = new MDElement(TagType::URLCAPTION, 0, true, $token['value']);
            $state['expectURL'] = true;
            unset_all_tags_open($opened, TagType::URLCAPTION);
            continue;
        }

        if ($token['type'] === Token::LPAREN && $state['expectURL']) {
            $result[] = new MDElement(TagType::URL, 0, false, $token['value']);
            set_tag_open($opened, TagType::URL);
            $state['expectURL'] = false;
            continue;
        }

        if ($token['type'] === Token::RPAREN && is_tag_open($opened, TagType::URL)) {
            $result[] = new MDElement(TagType::URL, 0, true, $token['value']);
            unset_all_tags_open($opened, TagType::URL);
            continue;
        }

        if (
                $token['type'] === Token::DASH
                /* Not in table cell as dash are used to separate cells */
                && !is_tag_open($opened, TagType::TABLECELL)
        ) {
            if (
                !is_tag_open($opened, TagType::STRIKETHROUGH)
                && peek_next_token_type($tokens, $i) !== Token::WS
            ) {
                $result[] = new MDElement(TagType::STRIKETHROUGH);
                set_tag_open($opened, TagType::STRIKETHROUGH);
                continue;
            }
            if (
                    is_tag_open($opened, TagType::STRIKETHROUGH) 
                    && peek_previous_token_type($tokens, $i) !== Token::WS
            ) {
                $result[] = new MDElement(TagType::STRIKETHROUGH, 0, true);
                unset_all_tags_open($opened, TagType::STRIKETHROUGH);
                continue;
            }
        }

        $previousElement = peek_previous_element($result, count($result));
        if ($previousElement->tag === TagType::LI && isEmptyValue($token['value'])) {
            continue;
        }
        /* *** What's left is TEXT *** */
        $mdElement = new MDElement(TagType::TEXT);
        if (
                $token['type'] === Token::WS 
                || $token['type'] === Token::TAB
                || $token['type'] === Token::BACKTICK
                || $token['type'] === Token::UNDERSCORE
                || $token['type'] === Token::DASH
                || $token['type'] === Token::ASTERISK
        ) {
            $mdElement->value = str_repeat($token['value'], $token['count']);
        } else {
            $mdElement->value = $token['value'];
        }
        $result[] = $mdElement;
    }

    return setLILevel($result);
}