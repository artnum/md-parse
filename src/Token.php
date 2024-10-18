<?php
declare(strict_types=1);

namespace md2ooxml;

enum Token {
    case NONE;
    case LN;
    case WS;
    case TAB;
    case HASH;
    case ASTERISK;
    case UNDERSCORE;
    case LBRACKET;
    case RBRACKET;
    case LPAREN;
    case RPAREN;
    case EXCLAMATION;
    case LT;
    case GT;
    case DIGIT;
    case DASH;
    case DOT;
    case BACKTICK;
    case TILDE;
    case PIPE;
    case COLON;
    case SEMICOLON;
    case DQUOTE;
    case QUOTE;
    case SQUOTE;
    case BACKSLASH;
    case SLASH;
    case AT;
    case AMPERSAND ;
    case LETTER ;
    case DEGREE ;
    case CR ;
    case PERCENT;
    case TEXT;
    case PLUS;
    case EQUAL;
    case QUESTION;
    case COMMA;
    case MINUS;
    case CARET;
    case DOLLAR;
}

