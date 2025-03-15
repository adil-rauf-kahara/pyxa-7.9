<?php

namespace App\Services\Chatbot;

use App\Helpers\Classes\Helper;
use Exception;

/**
 * Class LinkCrawler
 *
 * A simple web crawler for extracting content and links from a given website.
 *
 * @since 1.3
 */
class LinkCrawler
{
    /**
     * @var string the base URL of the website to crawl
     */
    private $baseUrl;

    /**
     * @var array an array to store crawled links
     */
    private $links = [];

    /**
     * @var int the maximum number of links to crawl
     */
    private $maxLinks = 30;

    /**
     * @var array an array of invalid paths to skip during crawling
     */
    private $invalidPaths = ['/cdn-cgi/'];

    /**
     * @var array an array to store contents of crawled pages
     */
    private $contents = [];

    /**
     * MagicAI_LinkCrawler constructor.
     *
     * @param  string  $url  the base URL of the website to crawl
     */
    public function __construct($url)
    {
        $this->baseUrl = $url;
    }

    /**
     * Initiate crawling process.
     */
    public function crawl($is_single = false)
    {
        if ($is_single) {
            $this->crawlSinglePage($this->baseUrl);
        } else {
            $this->crawlPage($this->baseUrl);
        }
    }

    /**
     * Recursively crawl a page and its links.
     *
     * @param  string  $url  the URL of the page to crawl
     */
    // private function crawlPage($url)
    // {
    //     $html = file_get_contents($url);

    //     $text = $this->stripTagsExceptContent($html);

    //     $this->contents[$url] = $text;

    //     preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/', $html, $matches);

    //     foreach ($matches[1] as $link) {
    //         $absoluteLink = $this->makeAbsoluteUrl($link);

    //         if ($absoluteLink && ! in_array($absoluteLink, $this->links) && $this->isSameDomain($absoluteLink, $this->baseUrl) && ! $this->hasInvalidPath($absoluteLink) && ! $this->isImage($absoluteLink)) {
    //             $this->links[] = $absoluteLink;
    //             if (count($this->links) >= $this->maxLinks) {
    //                 return;
    //             }

    //             try {
    //                 $this->crawlPage($absoluteLink);
    //             } catch (Exception $e) {
    //                 continue;
    //             }
    //         }
    //     }
    // }
    
    private function crawlPage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $html = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($html === false || !empty($error)) {
        throw new \Exception("Failed to fetch content from $url: " . $error);
    }

    $text = $this->stripTagsExceptContent($html);
    $this->contents[$url] = $text;

    preg_match_all('/<a\s+(?:[^>]*?\s+)?href="([^"]*)"/', $html, $matches);

    foreach ($matches[1] as $link) {
        $absoluteLink = $this->makeAbsoluteUrl($link);

        if ($absoluteLink && !in_array($absoluteLink, $this->links) && $this->isSameDomain($absoluteLink, $this->baseUrl) && !$this->hasInvalidPath($absoluteLink) && !$this->isImage($absoluteLink)) {
            $this->links[] = $absoluteLink;
            if (count($this->links) >= $this->maxLinks) {
                return;
            }

            try {
                $this->crawlPage($absoluteLink);
            } catch (\Exception $e) {
                continue;
            }
        }
    }
}


    /**
     * Recursively crawl a page
     *
     * @param  string  $url  the URL of the page to crawl
     */
    // private function crawlSinglePage($url)
    // {
    //     $html = file_get_contents($url);

    //     $text = $this->stripTagsExceptContent($html);

    //     $this->contents[$url] = $text;
    // }
    
//     private function crawlSinglePage($url)
// {
//     $options = [
//         'http' => [
//             'method' => 'GET',
//             'header' => "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36\r\n"
//         ]
//     ];
//     $context = stream_context_create($options);

//     $html = file_get_contents($url, false, $context);

//     $text = $this->stripTagsExceptContent($html);

//     $this->contents[$url] = $text;
// }

private function crawlSinglePage($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/100.0.4896.127 Safari/537.36");
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $html = curl_exec($ch);
    curl_close($ch);

    if ($html === false) {
        throw new \Exception("Failed to fetch content from $url");
    }

    $text = $this->stripTagsExceptContent($html);
    $this->contents[$url] = $text;
}


    /**
     * Make a relative URL absolute.
     *
     * @param  string  $url  the relative URL
     *
     * @return string|null the absolute URL, or null if unable to make absolute
     */
    private function makeAbsoluteUrl($url)
    {
        if (strpos($url, 'http') === 0 || strpos($url, 'https') === 0) {
            return $url;
        }

        if (strpos($url, '/') === 0) {
            return parse_url($this->baseUrl, PHP_URL_SCHEME) . '://' . parse_url($this->baseUrl, PHP_URL_HOST) . $url;
        }

        return null;
    }

    /**
     * Check if two URLs are of the same domain.
     *
     * @param  string  $url1  first URL
     * @param  string  $url2  second URL
     *
     * @return bool true if URLs are of the same domain, false otherwise
     */
    private function isSameDomain($url1, $url2)
    {
        $domain1 = parse_url($url1, PHP_URL_HOST);
        $domain2 = parse_url($url2, PHP_URL_HOST);

        return $domain1 === $domain2;
    }

    /**
     * Check if a URL contains any of the invalid paths.
     *
     * @param  string  $url  the URL to check
     *
     * @return bool true if URL contains invalid paths, false otherwise
     */
    private function hasInvalidPath($url)
    {
        foreach ($this->invalidPaths as $invalidPath) {
            if (strpos($url, $invalidPath) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a URL points to an image.
     *
     * @param  string  $url  the URL to check
     *
     * @return bool true if URL points to an image, false otherwise
     */
    private function isImage($url)
    {
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'apng', 'avif', 'svg', 'webp', 'ico', 'tiff'];
        $extension = pathinfo($url, PATHINFO_EXTENSION);

        return in_array(strtolower($extension), $imageExtensions);
    }

    /**
     * Strip HTML tags from content, except for specified elements.
     *
     * @param  string  $html  the HTML content to strip tags from
     *
     * @return string the stripped text content
     */
    private function stripTagsExceptContent($html)
    {
        $html = preg_replace('/<header\b[^>]*>.*?<\/header>/is', '', $html);
        $html = preg_replace('/<footer\b[^>]*>.*?<\/footer>/is', '', $html);

        $html = preg_replace('/<[^>]+class="[^"]*\bscreen-reader-text\b[^"]*"[^>]*>.*?<\/[^>]+>/is', '', $html);
        $html = preg_replace('/<[^>]+class="[^"]*\bscreen-reader-shortcut\b[^"]*"[^>]*>.*?<\/[^>]+>/is', '', $html);

        $text = Helper::strip_all_tags($html, true);

        return $text;
    }

    /**
     * Get the contents of crawled pages.
     *
     * @return array the contents of crawled pages
     */
    public function getContents()
    {
        return $this->contents;
    }

    /**
     * Get the crawled links.
     *
     * @return array the crawled links
     */
    public function getLinks()
    {
        return $this->links;
    }
}
