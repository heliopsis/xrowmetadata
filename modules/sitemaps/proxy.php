<?php
$Module = $Params['Module'];
$fileName = $Params['FileName'];
$dirname = eZSys::storageDirectory() . '/sitemap';

$file = $dirname . '/' . $fileName;
if(!file_exists($file) || is_dir($file) || $fileName == '.' || $fileName == '..'){
	//return 404
	$Module->handleError(eZError::KERNEL_MODULE_VIEW_NOT_FOUND, 'kernel');
} else {
	//return file contents
	$contents = file_get_contents($file);
	header( 'Content-Type: application/xml; charset=UTF-8' );
	header( 'Content-Length: '.strlen( $contents ) );
	print $contents;
}

eZExecution::cleanExit();
?>