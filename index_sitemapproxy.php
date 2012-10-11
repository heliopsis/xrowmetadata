<?php
require 'autoload.php';
eZSys::init( 'index.php', eZINI::instance()->variable( 'SiteAccessSettings', 'ForceVirtualHost' ) === 'true' );
$uri = eZURI::instance( eZSys::requestURI() );

$fileName = $uri->elements( true );
$dirname = eZSys::storageDirectory() . '/sitemap';

$file = $dirname . '/' . $fileName;
if(!file_exists($file) || is_dir($file) || $fileName == '.' || $fileName == '..'){
	//return 404
	header("HTTP/1.0 404 Not Found");
} else {
	//return file contents
	$contents = file_get_contents($file);
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'Content-Length: '.strlen( $contents ) );
	print $contents;
}

eZExecution::cleanExit();