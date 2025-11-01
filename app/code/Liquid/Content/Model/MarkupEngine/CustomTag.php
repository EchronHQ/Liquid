<?php

declare(strict_types=1);

namespace Liquid\Content\Model\MarkupEngine;

abstract class CustomTag
{
    public string $name;
    public readonly string $tag;
    // FULL block (HTML)
    public string $blockHtml = '';
    //Inner content (HTML)
    public string|null $contentHtml = null;
    // Used to represent Item
    public readonly string $placeholder;
    // extensible items
    public array $attributes = [];
    // Defines if tag is <tag /> format
    public bool $parsed = false;
    // Used in defining tag format
    public string $parsedContent = '';
    public bool $disabled = false;
    public bool $allowCache = false;
    // Regex Search Pattern
    public bool $cached = false;
    private bool $inlineClose = false;
    private string $closingTag = '>';
    private readonly string $tagSearch;

    /**
     * Creates Markup Object
     */
    public function __construct(string $strBody, string $tag, public readonly int $instanceId, int $tagIdentifier)
    {
        $this->placeholder = '------@@%' . $instanceId . '-' . $tagIdentifier . '%@@------';
        $this->tag = $tag;
        $this->blockHtml = $strBody;
        $this->name = \strtolower(\str_replace(__NAMESPACE__ . "\\", "", \get_class($this)));
        if (str_ends_with($strBody, '/>')) {
            $this->inlineClose = true;
            $this->closingTag = "\/>";
        }
        $this->tagSearch = "/<($this->tag)\s*([^$this->closingTag]*)/";

        $this->build($strBody);
    }

    abstract public function render(): string;

//    /**
//     * Magic Method to return readonly properties
//     *
//     * @param string $var property name
//     * @return mixed property
//     */
//    public function __get(string $var): mixed
//    {
//        die('Magic get of ' . $var);
////        // Add in_array to restrict access
////        if (isset($this->$var)) return $this->$var;
////        if (isset($this->attributes->$var)) return $this->attributes->$var;
////        return null;
//    }

//    /**
//     * Magic Method to set readonly properties
//     *
//     * @param string $var name of variable to set
//     * @param string $val value to apply
//     */
//    public function __set(string $var, mixed $val): void
//    {
////        if (in_array($var, ['parsed', 'parsedcontent', 'block', 'content', 'placeholder', 'innermarkers'])) {
////            $this->$var = $val ?? null;
////        } else {
//        die('Set' . $var);
////        }
//    }
//
//    public function __isset(string $var): bool
//    {
//        die('Mageic isset ' . $var);
//    }

    private function build(string $strBody): void
    {


        if (!str_starts_with($strBody, '<' . $this->tag)) {
            $this->name = '___text';
            $this->contentHtml = $this->blockHtml;
            return;
        }

        if ($strBody[1] === '/') {
            $this->name = '---ERROR---';
            return;
        }
        $matches = [];
        if (\preg_match_all($this->tagSearch, $this->blockHtml, $matches) > 0) {
            $attribute_string = $matches[2][0];
            if (!$this->inlineClose) {
                $begin_len = \strlen($matches[0][0]);
                $end_len = \strlen("</" . $this->tag . ">");
                $this->contentHtml = \substr($strBody, $begin_len + 1, \strlen($matches[0][0]) - $begin_len - $end_len);
            }

            //            $attributes = [];
            if (\\preg_match_all("!([_\-A-Za-z0-9]*)(=\"|=\')([^\"|\']*)(\"|\')!is", $attribute_string, $attributes) > 0) {
                foreach ($attributes[0] as $key => $row) {
                    $this->attributes[$attributes[1][$key]] = $attributes[3][$key];
                }
                /** @todo: template engine */
                /*if(isset($this->attributes['template']) === true)
                {
                    $template = $this->_options['template_directory'].$tag['name'].DIRECTORY_SEPARATOR.$this->attributes['template'].'.html';
                    if(is_file($template) === false)
                    {
                        $this->attributes['_template'] = $template;
                    }
                    else
                    {
                        $this->attributes['template'] = $template;
                    }
                }*/
            }
        }
        //        $this->attributes = (object)$this->attributes;
    }
}
