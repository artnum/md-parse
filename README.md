# md2ooxml

Convert a markdown text into a docx using PHPOffice. Work in progress ...

## Tokenizer and parser

Tokenizer and parser are made in a way to be used independantly and not tied
with the output.

Tokenizer use multibyte string function from PHP so it should support unicode
without any issue.

## Output to docx

Output to docx use custom styles such as `md-block-h1`, `md-block-h2`,
`md-block-quote`, ...so you start with a a template document with all styles
defined within and have your output match the visual design you want.