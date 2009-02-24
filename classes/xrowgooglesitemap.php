<?php

class xrowGoogleSiteMap
{
    protected $dom;
    protected $root;
    const BASENAME = 'sitemap';
    const SUFFIX = 'xml';

    /**
     * 
     */
    function __construct()
    {
        // Create the DOMnode
        $this->dom = new DOMDocument( "1.0", "UTF-8" );
        
        // Create DOM-Root (urlset)
        $this->root = $this->dom->createElement( 'urlset' );
        $this->root->setAttribute( "xmlns", "http://www.google.com/schemas/sitemap/0.84" );
        $this->root->setAttribute( "xmlns:xsi", "http://www.w3.org/2001/XMLSchema-instance" );
        $this->root->setAttribute( "xsi:schemaLocation", "http://www.google.com/schemas/sitemap/0.84 http://www.google.com/schemas/sitemap/0.84/sitemap.xsd" );
        $this->dom->appendChild( $this->root );
    }

    function add( $url, $modified, $change = false, $priority = false )
    {
        $modified = date( "c", $modified );
        
        $node = $this->dom->createElement( "url" );
        $subNode = $this->dom->createElement( 'loc', $url );
        $node->appendChild( $subNode );
        $date = $this->dom->createTextNode( $urlAlias );
        $subNode->appendChild( $date );
        $subNode = $this->dom->createElement( 'lastmod', $modified );
        $node->appendChild( $subNode );
        
        if ( isset( $change ) )
        {
            $subNode = $this->dom->createElement( 'changefreq', $change );
            $node->appendChild( $subNode );
        }
        if ( isset( $priority ) )
        {
            $subNode = $this->dom->createElement( 'priority', $priority );
            $node->appendChild( $subNode );
        }
        // append to root node
        $this->root->appendChild( $node );
    }

    function save( $filename = 'sitemap.xml' )
    {
        return $this->dom->save( $filename );
    }

}

?>