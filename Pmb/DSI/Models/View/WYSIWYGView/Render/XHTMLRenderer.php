<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: XHTMLRenderer.php,v 1.1.2.5 2024/05/15 09:21:44 jparis Exp $

namespace Pmb\DSI\Models\View\WYSIWYGView\Render;

use Pmb\Common\Helper\Helper;

class XHTMLRenderer extends HTML5Renderer
{
    protected function renderBlockElement($currentElement)
    {
        if ($currentElement->style->flexDirection == "column") {
            $html = "<table width='100%' height='100%' style='!!style!!'>!!content!!</table>";
        } else {
            $html = "<table width='100%' height='100%'><tr style='!!style!!'>!!content!!</tr></table>";
        }


        $width = 100;
        if (!empty($currentElement->blocks)) {
            $width = 100 / count($currentElement->blocks);
            $width = round($width) . "%";
        }

        $content = '';
        foreach ($currentElement->blocks as $block) {

            if(isset($block->widthEnabled) && $block->widthEnabled) {
                $width = $block->style->width;
            }

            if ($currentElement->style->flexDirection == "column") {
                $content .= "<tr><td width='{$width}'>!!content!!</td></tr>";
            } else {
                $content .= "<td width='{$width}'>!!content!!</td>";
            }

            if(isset($block->widthEnabled) && $block->widthEnabled) {
                $width = "";
            }

            $content = str_replace('!!content!!', $this->render($block), $content);
        }

        return str_replace(
            ['!!style!!', '!!content!!'],
            [$this->getStyleString($currentElement->style), $content],
            $html
        );
    }

    protected function renderVideoElement($currentElement)
    {
        return "<!-- videos not supported -->";
    }

    protected function getStyleString($style): string
    {
        if (!is_object($style)) {
            return "";
        }

        if (isset($style->block)) {
            $style = $style->block;
        }

        $style = get_object_vars($style);
        $style = $this->convertToXHTML($style);

        array_walk($style, function (&$value, $attribute) {
            $value = "{$attribute}:{$value}";
        });

        return implode(';', $style);
    }

    protected function convertToXHTML($style)
    {
        $convertedStyle = array();
        foreach ($style as $attribute => $value) {
            $attribute = Helper::camelize_to_kebab($attribute);

            switch ($attribute) {

                case 'display':
                    if ($value === 'flex') {
                        // Attention le display: table fait bug les padding
                        //$convertedStyle['display'] = 'table';
                        
                        unset($convertedStyle['display']);
                        
                    } else {
                        $convertedStyle['display'] = $value;
                    }
                    break;

                case 'flex':
                    if(!isset($style['width']) || $style['width'] == "") {
                        $convertedStyle['width'] = '100%';
                    }

                    $convertedStyle['height'] = '100%';
                    break;

                case 'flex-grow':
                    if(!isset($style['max-width']) || $style['max-width'] == "") {
                        $convertedStyle['min-width'] = '100%';
                    }

                    $convertedStyle['min-height'] = '100%';
                    break;

                case 'flex-direction':
                    // not compatible Xhtml
                    break;

                case 'justify-content':
                    switch ($value) {
                        default:
                        case 'start':
                            $convertedStyle['text-align'] = 'left';
                            break;
                        case 'center':
                            $convertedStyle['text-align'] = 'center';
                            break;
                        case 'end':
                            $convertedStyle['text-align'] = 'right';
                            break;
                    }
                    break;

                case 'align-items':
                    switch ($value) {
                        default:
                        case 'start':
                            $convertedStyle['vertical-align'] = 'top';
                            break;
                        case 'center':
                            $convertedStyle['vertical-align'] = 'middle';
                            break;
                        case 'end':
                            $convertedStyle['vertical-align'] = 'bottom';
                            break;
                    }
                    break;

                default:
                    $convertedStyle[$attribute] = $value;
                    break;
            }
        }
        return $convertedStyle;
    }
}