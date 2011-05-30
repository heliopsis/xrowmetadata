<?php

$ini = eZINI::instance( 'xrowmetadata.ini' );

$imp = new DomImplementation( );
$dom = $imp->createDocument( 'http://www.sitemaps.org/schemas/sitemap/0.9', 'sitemapindex' );
$dom->version = '1.0';
$dom->encoding = 'UTF-8';
$attr = $dom->createAttributeNS( 'http://www.w3.org/2001/XMLSchema-instance', 'xsi:schemaLocation' );
$attr->value = 'http://www.sitemaps.org/schemas/sitemap/0.9' . ' ' . 'http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd';
$dom->documentElement->appendChild( $attr );
$sitemap = $dom->createElement( 'sitemap', 'asdasd' );
$attr->appendChild( $sitemap );
$dirname = eZSys::storageDirectory() . '/sitemap';
$dir = new DirectoryIterator( $dirname );
foreach ( $dir as $file2 )
{
    if ( ! $file2->isDot() and !$file2->isDir() )
    {
		$dt = new DateTime( "@" . $file2->getMTime() );
		$sitemap = $dom->createElement( 'sitemap' );
		$loc = $dom->createElement( 'loc', 'http://' . $_SERVER['HTTP_HOST'] . '/' . $file2->getFilename() );
		$lastmod = $dom->createElement( 'lastmod', $dt->format( DateTime::W3C ) );
		$sitemap->appendChild( $loc );
		$sitemap->appendChild( $lastmod );
		$dom->documentElement->appendChild( $sitemap );
    }
}
unset( $dir );
$content = $dom->saveXML();

// Set header settings
header( 'Content-Type: application/xml; charset=UTF-8' );
header( 'Content-Length: ' . strlen( $content ) );
header( 'X-Powered-By: eZ Publish' );

while ( @ob_end_clean() );

echo $content;

eZExecution::cleanExit();
?>