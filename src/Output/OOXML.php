<?php
declare(strict_types=1);

namespace md2ooxml\Output;

use md2ooxml\TagType;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Style\ListItem;

class OOXML {
    public Section $section;
    
    function __construct(Section $section)
    {
        $this->section = $section;
    }

    function write(TextRun $run, string $text, string $fontStyle = 'md-text') {
        $run->addText(htmlspecialchars($text), $fontStyle);
    }

    function output(array $elements) {
        $run = null;
        $currentTable = null;
        $fontStyle = 'md-text';
        $listPStyle = 'md-list-ul';
        $listStyle = 'md-ol-numberging';
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
                    if ($element->close) { break; }
                    $listStyle = 'md-ul-numbering';
                    $listPStyle = 'md-list-ul';
                    break;
                case TagType::OL:
                    if ($element->close) { break; }
                    $listStyle = 'md-ol-numbering';
                    $listPStyle = 'md-list-ol';
                    break;
                case TagType::LI:
                    if ($element->close) { break; }
                    $j = 1;
                    $text = '';
                    while ($elements[$i + $j]->tag === TagType::TEXT) {
                        $text .= $elements[$i + $j]->value;
                        $j++;
                    }
                    $this->section->addListItem($text, 0, null, $listStyle, $listPStyle);
                    $i = $i + $j + 1;
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
                    $i = $i + $j + 1;
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