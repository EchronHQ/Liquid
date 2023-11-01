<?php

declare(strict_types=1);

namespace Liquid\Content\Helper;

use Liquid\Content\Model\MarkupEngine\BlockTag;
use Liquid\Content\Model\MarkupEngine\CustomTag;
use Liquid\Content\Model\MarkupEngine\CustomTagConcrete;
use DI\Container;
use Psr\Log\LoggerInterface;

class MarkupEngine
{
    private bool $debug = true;
    private static int $engineInstanceId = 0;
    private array $registeredTags = [];
    private bool $cacheTags = false;
    private bool $sniffForBuriedTags = true;
    private string|null $customCacheTagClass = null;
    private string $_searchReg = '';
    private string $cacheDirectory = '';

    public function __construct(
        private readonly Container       $container,
        private readonly LoggerInterface $logger
    ) {
    }

    public function registerTag(string $tag, string $className, array $arguments = []): void
    {

        $this->registeredTags[] = [
            'tag' => $tag,
            'class' => $className,
            'arguments' => $arguments,
        ];
    }

    /**
     * Processes a tag by loading
     * @access private
     * @param CustomTag $tag The tag to parse.
     * @return string The content of the tag.
     */


    private function renderTag(CustomTag $tag): string
    {
        if ($tag->disabled) {
            // return empty for disabled tag
            if ($this->debug) {
                return '[Tag `' . $tag->tag . '` Disabled]';
            }
            return '';


        }
        $tag_data = false;

        if ($this->cacheTags && $tag->allowCache) { // Cache
            if ($this->customCacheTagClass !== null) {
                $tag_data = call_user_func_array([$this->customCacheTagClass, 'getCache'], [$tag]);
            } else {
                $cache_file = $this->cacheDirectory . md5(serialize($tag));
                if (is_file($cache_file) === true) {
                    $tag_data = file_get_contents($cache_file);
                }
            }
            if ($tag_data) {
                $tag->cached = true;
                return $tag_data;
            }
        }
        $tag_data = $tag->render();


        if ($tag_data) {    // Find all buried tags
            if ($this->sniffForBuriedTags && $this->getLastTag($tag_data) !== null) {
                // we have the possibility of buried tags so lets parse the tag_data

                $tag_data = $this->parse($tag_data);

            }

            if ($this->cacheTags === true && $tag->allowCache === true) {
                if ($this->customCacheTagClass !== null) {
                    call_user_func_array([$this->customCacheTagClass, 'cache'], [$tag, $tag_data]);
                } else {
                    file_put_contents($this->cacheDirectory . md5(serialize($tag)), $tag_data, LOCK_EX);
                }
            }
        }
        return $tag_data;
    }

    /**
     * Utility Method to search for last allowable Tag not already processed
     *
     * @param string $subject
     * @return array{x:string,pos:int}|null of matched items
     */
    private function getLastTag(string $subject): array|null
    {
        $PregMatch = '/' . $this->_searchReg . '/';

        /** @var false|int $matchCount */
        $matchCount = preg_match_all($PregMatch, $subject, $matches, PREG_OFFSET_CAPTURE);
        /** @var string[][][] $matches */
        if (!$matchCount) {
            return null;
        }

        $z = $matches[0];
        $last = $z[count($z) - 1];

        return ['x' => $last[0], 'pos' => (int)$last[1]];
    }

    /**
     * Parses the source for any custom tags.
     * @access public
     * @param string $source
     * @return string The parsed $source value.
     */
    public function parse(string $source): string
    {

        ++self::$engineInstanceId;              // increment the parse count so it has unique identifiers

        $this->buildReg();
        $tags = $this->processTags($source);    // Scrub for all tags

        //        $debug = [];
        //        if (true) {
        //            $l = [];
        //            foreach ($tags as $tag) {
        //                $l[] = ['tag' => $tag->tag, '' => $tag->contentHtml];
        //            }
        //            $debug = ['tags' => $l];
        //
        //            //   echo json_encode($debug, JSON_PRETTY_PRINT);
        //        }
        $renderedOutput = $this->renderTags($tags);


        return $renderedOutput;
    }

    /** @var  CustomTag[] */
    private array $tags = [];

    private function buildReg(): void
    {
        $searchRegex = "";
        foreach ($this->registeredTags as $registeredTag) {
            if ($searchRegex !== "") {
                $searchRegex .= "|";
            }
            $searchRegex .= '\b' . $registeredTag['tag'] . '\b';
        }
        $this->_searchReg = "<($searchRegex)";
    }

    /**
     * Searches and parses a source for custom tags.
     * @access public
     * @param string $source The source to search for custom tags in.
     * @return CustomTag[] An array of found tags.
     */
    private function processTags(string $source): array
    {
        /** @var CustomTag[] $tags */
        $tags = [];

        // Sets Open Pos to end of HTML ($source)
        $eot = strlen($source);

        while ($eot !== null) {
            $currentSource = substr($source, 0, $eot);    // Remaining HTML (moving Up)
            $lastTag = $this->getLastTag($currentSource);       // Position of "Opener"

            if ($lastTag === null) { // No More Tags found


                $tag = new CustomTagConcrete($source, 'xxxxx', self::$engineInstanceId, count($tags));
                $this->tags[self::$engineInstanceId . '-' . count($tags)] = $tag;
                $tags[] = $tag;
                break;
            }

            // Tag found (start from last find)
            $tagName = str_replace('<', '', $lastTag['x']);
            $eot = $lastTag['pos'];
            $closer = "</$tagName>";
            $currentSource = substr($source, $eot); // HTML from Last occurence till end or Last processed Tag
            $nextDom = strpos($currentSource, '<', 1); //Start of Next DOM Tag
            $nextClosingTag = strpos($currentSource, '/' . '>'); //Close Bracket Loc

            if ($nextClosingTag !== false && $nextClosingTag < $nextDom) {
                // Closing DOM is before the next DOM element (indicates <tag /> format)
                $tagClosePosition = $nextClosingTag + 2; // Update TagClose to include />
            } else {
                // Traditional <tag></tag> format
                $tagCloseBeginning = strpos($currentSource, $closer);
                $tagClosePosition = strpos($currentSource, '>', $tagCloseBeginning) + 1;
            }

            $tag_source = substr($currentSource, 0, $tagClosePosition);

            $tagData = $this->getTagData($tagName);
            $tagClass = $tagData['class'];
            if ($tagClass === null || !class_exists($tagClass) || !$this->container->has($tagClass)) {
                // TODO: is this desired behaviour?
                $tag = new CustomTagConcrete($tag_source, $tagName, self::$engineInstanceId, count($tags));

                $this->logger->warning('[MarkupEngine] class `' . $tagClass . '` not found for tag `' . $tagName . '`');
            } else {
                $block = $this->container->make($tagClass, $tagData['arguments']);


                $tag = new BlockTag($tag_source, $tagName, self::$engineInstanceId, count($tags));
                $tag->setXBlock($block);
            }

            $this->tags[self::$engineInstanceId . '-' . count($tags)] = $tag;
            $tags[] = $tag; // Append Tag (stdClass)


            $source = substr($source, 0, $eot) . $tag->placeholder . substr($source, $eot + $tagClosePosition); // Update Source for next request
        }
        return $tags;
    }

    private function getTagData(string $tag): array|null
    {
        // TODO: maybe replace this with a lookup map for better performance
        foreach ($this->registeredTags as $registeredTag) {
            if ($registeredTag['tag'] === $tag) {
                return $registeredTag;
            }
        }
        return null;
    }

    /**
     * Loops and parses the found custom tags.
     * @access private
     * @param CustomTag[] $tags An array of found custom tag data.
     * @return string Returns false if there are no tags, string otherwise.
     */
    private function renderTags(array $tags): string
    {
        if (count($tags) === 0) {
            return '';
        }

        foreach ($tags as $key => $tag) {
            // Loop through Tags
            // TODO: what does delayed means? Are we ever going to use it?
            //            if ($tag->attributes->delayed ?? false) {
            //                continue;
            //            }

            $hasBuried = preg_match_all('!------@@%([0-9\-]+)%@@------!', $tag->contentHtml, $info);


            $containers = [];
            $replacements = [];

            if ($hasBuried > 0) {

                $containers = $info[0];
                $indexes = $info[1];
                foreach ($indexes as $key2 => $tagIdentifier) {
                    if (!isset($this->tags[$tagIdentifier])) {
                        $this->logger->error('Tag to replace `' . $tagIdentifier . '` not found');
                        $replacements[$key2] = '[ERROR]';

                    } elseif (!$this->tags[$tagIdentifier]->parsed) {

                        if ($this->tags[$tagIdentifier]->blockHtml !== null) {
                            $blockHtml = preg_replace('/ delayed="true"/', '', $this->tags[$tagIdentifier]->blockHtml, 1);
                            if ($tag->blockHtml !== null) {
                                $tag->blockHtml = str_replace($containers[$key2], $blockHtml, $tag->blockHtml);
                            }
                            $tag->contentHtml = str_replace($containers[$key2], $blockHtml, $tag->contentHtml);
                        }
                    } else {
                        $replacements[$key2] = $this->tags[$tagIdentifier]->parsedContent;
                    }
                }

            }

            if ($tag->name === '___text') {
                // Plain Text (no rendering)
                $body = $tag->contentHtml;
            } else {
                // Tag for processing
                $body = $this->renderTag($tag);

            }
            if ($hasBuried > 0) {
                $body = str_replace($containers, $replacements, $body);
            }


            $tag->parsedContent = $body;
            $tag->parsed = true;


        }
        // Return last tag (why last one? because that should be the "root"?)
        return $tags[count($tags) - 1]->parsedContent;
    }
}
