<?php

/*
Plugin Name: Link Images To Post
Description: Wraps images in a post with a link to that post
Version: 0.1
Author: Clayton Leis
Author URI: http://www.claytonleis.com
License: GPLv2 or later.
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class LinkImagesToPost {

    function __construct()
    {
        add_filter( 'the_content', array( $this, 'linkImagesToPost') );
    }

    function linkImagesToPost( $content ){

        if( !( is_archive() || is_home() ) )
            return $content;

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML("<div>$content</div>");
		$doc = $this->removeExtraTags($doc);

        // modify or create the <a>
        $images = $doc->getElementsByTagName('img');
        foreach($images as $image) {
            if(strpos($image->getAttribute('class'), 'litp-ignore') !== false)
                continue;

            $link = $this->getWrappingLink($image);
            if(!$link) {
                // make our own wrapping link
                $link = $doc->createElement('a');
                $image->parentNode->replaceChild($link, $image);
                $link->appendChild($image);
            }
            $link->setAttribute('href', get_permalink());
            $link->setAttribute('target', '');
            $link->setAttribute('class', $link->getAttribute('class') . ' litp-linked');
        }

        return $doc->saveHTML();
    }

    /*
     * Get wrapping <a> element
     */
    function getWrappingLink($domElement) {
        $ancestor = $domElement->parentNode;
        while($ancestor) {
            if($ancestor->nodeName === 'a'){
                return $ancestor;
            }
            $ancestor = $ancestor->parentNode;
        }
        return null;
    }

	/*
	 * Remove doctype, html, and body tags
	 */
    function removeExtraTags(DOMDocument $domDoc) {
	    $container = $domDoc->getElementsByTagName('div')->item(0);
	    $container = $container->parentNode->removeChild($container);
	    while ($domDoc->firstChild) {
		    $domDoc->removeChild($domDoc->firstChild);
	    }
	    while ($container->firstChild ) {
		    $domDoc->appendChild($container->firstChild);
	    }
	    return $domDoc;
    }

}

$cl_link_images_to_post = new LinkImagesToPost();

