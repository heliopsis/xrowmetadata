<?php
print 'ddgfgf';
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
$siteAccessList = array();
$googlesitemapsINI = eZINI::instance( 'googlesitemaps.ini' );

if ( $googlesitemapsINI->hasVariable( 'SiteAccessSettings', 'RelatedSitemaps' ) )
{
	$relatedSitemaps = $googlesitemapsINI->variable( 'SiteAccessSettings', 'RelatedSitemaps' );
	if( !empty( $relatedSitemaps ) )
	{
		$siteAccessList = $relatedSitemaps;
	}
}

if( empty( $siteAccessList ) )
{
	$siteAccessList = array( eZSiteAccess::current() );
}

foreach ( $siteAccessList as $siteAccess )
{
	$file = $dirname . '/' . $siteAccess . '_sitemap.xml';
    if ( file_exists( $file ) )
    {
		$dt = new DateTime( "@" . filemtime( $file ) );
		$sitemap = $dom->createElement( 'sitemap' );
		$loc = $dom->createElement( 'loc', 'http://' . $_SERVER['HTTP_HOST'] . '/' . basename( $file ) );
		$lastmod = $dom->createElement( 'lastmod', $dt->format( DateTime::W3C ) );
		$sitemap->appendChild( $loc );
		$sitemap->appendChild( $lastmod );
		$dom->documentElement->appendChild( $sitemap );
    }
}
unset( $dir );
$content = $dom->saveXML();

// Set header settings
/*header( 'Content-Type: application/xml; charset=UTF-8' );
header( 'Content-Length: ' . strlen( $content ) );
header( 'X-Powered-By: eZ Publish' );*/

while ( @ob_end_clean() );

echo $content;

eZExecution::cleanExit();
?>