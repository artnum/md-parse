<?php
declare(strict_types=1);

namespace md2ooxml\Output;

use md2ooxml\TagType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Style\ListItem;
use phpseclib3\Math\BigInteger\Engines\PHP;

class OOXML {
    public Section $section;
    public PhpWord $word;
    function __construct(PhpWord $word, ?Section $section = null)
    {

        $this->word = $word;
        if ($section === null) {
            $section = $word->addSection('md-section');
        } else {
            $this->section = $section;
        }
    }

    function write(TextRun $run, string $text, string $fontStyle = 'md-text') {
        $run->addText(htmlspecialchars($text), $fontStyle);
    }

    function cm2twip (float $cm) {
        return round($cm * 1000 / 17.64, 0, PHP_ROUND_HALF_EVEN) * 10;
    }

    /* based on default value in word 365 */
    const DEFAULT_HANGING_TWIP  = 360;
    const DEFAULT_LEFT_L1_TWIP  = 720;
    const DEFAULT_LEFT_L2_TWIP  = 1440;
    const DEFAULT_LEFT_L3_TWIP  = 2160;
    const DEFAULT_LEFT_L4_TWIP  = 2880;
    const DEFAULT_LEFT_L5_TWIP  = 3600;
    const DEFAULT_LEFT_L6_TWIP  = 4320;
    const DEFAULT_LEFT_L7_TWIP  = 5040;
    const DEFAULT_LEFT_L8_TWIP  = 5760;
    const DEFAULT_LEFT_L9_TWIP  = 6480;
    

    function output(array $elements) {
        $run = null;
        $currentTable = null;
        $fontStyle = 'md-text';
        $listPStyle = 'md-list-ul';
        $listStyle = 'md-ol-numberging';

        /* numbering style are not copied from template */
        $baseNumberingStyle =         [
            'type' => 'multilevel',
            'levels' => [
                ['format' => 'decimal',     'text' => '%1. ', 'left' => self::DEFAULT_LEFT_L1_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerLetter', 'text' => '%2. ', 'left' => self::DEFAULT_LEFT_L2_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerRoman',  'text' => '%3. ', 'left' => self::DEFAULT_LEFT_L3_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'decimal',     'text' => '%4. ', 'left' => self::DEFAULT_LEFT_L4_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerLetter', 'text' => '%5. ', 'left' => self::DEFAULT_LEFT_L5_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerRoman',  'text' => '%6. ', 'left' => self::DEFAULT_LEFT_L6_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'decimal',     'text' => '%7. ', 'left' => self::DEFAULT_LEFT_L7_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerLetter', 'text' => '%8. ', 'left' => self::DEFAULT_LEFT_L8_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                ['format' => 'lowerRoman',  'text' => '%9. ', 'left' => self::DEFAULT_LEFT_L9_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],

            ],
        ];
        /*
        $this->word->addNumberingStyle(
            'md-ol-item',
            [
                'type' => 'multilevel',
                'levels' => [
                    ['format' => 'bullet', 'text' => '•', 'left' => self::DEFAULT_LEFT_L1_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => 'o', 'left' => self::DEFAULT_LEFT_L2_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => '🞍', 'left' => self::DEFAULT_LEFT_L3_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => '•', 'left' => self::DEFAULT_LEFT_L4_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => 'o', 'left' => self::DEFAULT_LEFT_L5_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => '🞍', 'left' => self::DEFAULT_LEFT_L6_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => '•', 'left' => self::DEFAULT_LEFT_L7_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => 'o', 'left' => self::DEFAULT_LEFT_L8_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP],
                    ['format' => 'bullet', 'text' => '🞍', 'left' => self::DEFAULT_LEFT_L9_TWIP, 'hanging' => self::DEFAULT_HANGING_TWIP]
                ]
            ]
        );     */      

        $listOLIdCount = 0;
        $pStyle = 'md-ol-item';
        $elementCount = count($elements);
        for ($i = 0; $i < $elementCount; $i++) {
            $element = $elements[$i];
            switch ($element->tag) {
                case TagType::STRONG:
                    if ($element->close) {
                        if ($fontStyle === 'md-text-strong') {
                            $fontStyle = 'md-text';
                        } else if ($fontStyle === 'md-text-strong-italic') {
                            $fontStyle = 'md-text-italic';
                        }  else {
                            $fontStyle = 'md-text-strong';
                        }
                    } else {
                        if ($fontStyle === 'md-text-italic') {
                            $fontStyle = 'md-text-strong-italic';
                        } else {
                            $fontStyle = 'md-text-strong';
                        }
                    }
                    break;
                case TagType::EMPHASIS:
                    if ($element->close) {
                        if ($fontStyle === 'md-text-italic') {
                            $fontStyle = 'md-text';
                        } else if ($fontStyle === 'md-text-strong-italic') {
                            $fontStyle = 'md-text-strong';
                        } else {
                            $fontStyle = 'md-text-italic';
                        }
                    } else {
                        if ($fontStyle === 'md-text-strong') {
                            $fontStyle = 'md-text-strong-italic';
                        } else {
                            $fontStyle = 'md-text-italic';
                        }
                    }
                    break;
                    
                case TagType::HEADER:
                    if ($element->close) { $run = null; break; }
                    $run = $this->section->addTextRun('md-block-h' . $element->level);
                    break;
                case TagType::PRE:
                    if ($element->close) { $run = null; break; }
                    $run = $this->section->addTextRun('md-block-pre');
                    break;
                case TagType::QUOTE:
                    if ($element->close) { $run = null; break; }
                    $run = $this->section->addTextRun('md-block-quote');
                    break;
                case TagType::TEXT:
                    if ($run === null) {
                        $run = $this->section->addTextRun('md-block-paragraph');
                    }
                    $this->write($run, $element->value, $fontStyle);
                    break;
                case TagType::UL:
                    if ($element->close) {
                        $run = null;
                        break;
                    }
                    $listStyle = ListItem::TYPE_BULLET_FILLED;
                    $pStyle = 'md-list-ul';
                    break;
                case TagType::OL:
                    if ($element->close) {
                        $run = null;
                        break; 
                    }
                    $listStyle = 'md-ol-item' . $listOLIdCount++;
                    $this->word->addNumberingStyle(
                        $listStyle,
                        $baseNumberingStyle
                    );
                    
                    $pStyle = 'md-list-ol';
                    break;
                case TagType::LI:
                    if ($element->close) {
                        $run = null;
                        break;
                    }
                    $run = $this->section->addListItemRun($element->level, $listStyle, $pStyle);
                    break;
                case TagType::LINEBREAK:
                    if ($run === null) { break; }
                    $run->addTextBreak();
                    break;
                case TagType::URLCAPTION:
                    if ($run === null) { break; }
                    if ($element->close) { break; }
                    $j = 1;
                    $text = '';
                    while ($elements[$i + $j]->tag !== TagType::URLCAPTION) {
                        if ($elements[$i + $j]->tag === TagType::LINEBREAK) {
                            $j++;
                            continue;
                        }
                        $text .= $elements[$i + $j]->value;
                        $j++;
                    }
                    while ($elements[$i + $j]->tag !== TagType::URL) {
                        $j++;
                    }
                    while ($elements[$i + $j]->tag !== TagType::TEXT) {
                        $j++;
                    }
                    $url = '';
                    while ($elements[$i + $j]->tag !== TagType::URL) {
                        $url .= $elements[$i + $j]->value;
                        $j++;
                    }
                    $i = $i + $j;
                    $run->addLink($url,$text, 'md-link');
                    break;
                case TagType::TABLE:
                    if ($element->close) { break; }
                    $currentTable = $this->section->addTable();
                    break;
                case TagType::TABLEHEADER:
                case TagType::TABLEROW:
                    if ($currentTable === null) { break; }
                    if ($element->close) { break; }
                    $currentTable->addRow();
                    break;
                case TagType::TABLECELL:
                    if ($currentTable === null) { break; }
                    if ($element->close) { break; }
                    $j = 1;
                    $text = '';
                    while ($elements[$i + $j]->tag !== TagType::TABLECELL) {
                        $text .= $elements[$i + $j]->value;
                        $j++;
                    }
                    $currentTable->addCell()->addText($text);
                    $i = $i + $j;
                    break;
                
            }
        }
    }
}