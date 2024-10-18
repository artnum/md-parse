<?php

use PhpOffice\PhpWord\Element\Section;

function msword (Section $section, array $tokens) {
    $block = $section->addTextRun('md-block-paragraph');
    $currentBlock = '';
    $currentBlockStyle = 'md-block-paragraph';
    $currentFontStyle = 'md-text';

    $open = [];
    foreach ($tokens as $token) {
        echo $token['type'] . ' ' . $token['value'] . PHP_EOL;
        switch ($token['type']) {

            /* block types */
            case 'quote':
                echo 'RUN quote' . PHP_EOL;
                if ($currentBlock === 'quote') {
                    break;
                }
                $currentBlockStyle = 'md-block-quote';
                $block = $section->addTextRun('md-block-quote');
                $currentBlock = 'quote';
                break;
            case 'header':
                echo 'PROCESSING HEADER' . PHP_EOL;
                $currentBlock = 'header';
                $currentBlockStyle = 'md-block-h' . $token['count'];
                $block = $section->addTextRun('md-block-h' . $token['count']);
                break;

            case 'paragraph':
                echo 'RUN paragraph' . PHP_EOL;
                $currentBlockStyle = 'md-block-paragraph';
                $block = $section->addTextRun('md-block-paragraph');
                $currentBlock = 'paragraph';
                break;

            case 'indent':
                echo 'RUN indent' . PHP_EOL;
                $currentBlockStyle = 'md-block-pre';
                $block = $section->addTextRun('md-block-pre');
                $currentBlock = 'indent';
                break;
                
            default:
            case 'text':
                echo 'RUN text' . PHP_EOL;
                $style = 'md-text';
                if (in_array('strong', $open) && !in_array('italic', $open)) {
                    $style = 'md-text-strong';
                }
                if (in_array('italic', $open) && !in_array('strong', $open)) {
                    $style = 'md-text-italic';
                }
                if (in_array('italic', $open) && in_array('strong', $open)) {
                    $style = 'md-text-strong-italic';
                }   
                $block->addText(htmlspecialchars($token['value']), $style, $currentBlockStyle);
                break;

            case 'italic':
                if (in_array('italic', $open)) {
                    for ($i = count($open) - 1; $i >= 0; $i--) {
                        if ($open[$i] === 'italic') {
                            unset($open[$i]);
                            $open = array_values($open); // reindex
                            break;
                        }
                    }
                } else {
                    $open[] = 'italic';
                }
                break;

            case 'strong':
                if (in_array('strong', $open)) {
                    for ($i = count($open) - 1; $i >= 0; $i--) {
                        if ($open[$i] === 'strong') {
                            unset($open[$i]);
                            $open = array_values($open); // reindex
                            break;
                        }
                    }
                } else {
                    $open[] = 'strong';
                }
                break;

            case 'si_unit':
                echo 'RUN si_unit' . PHP_EOL;
                $block->addText($token['value'], 'md-text-si-unit', $currentBlockStyle);
                break;


            case 'digit':
                echo 'RUN digit' . PHP_EOL;
                $block->addText($token['value'], 'md-text-number', $currentBlockStyle);
                break;

            case 'nl':
                $open = [];
                echo 'RUN nl' . PHP_EOL;
                $block->addTextBreak();
                break;
        }
    }

}