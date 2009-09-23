<?php
if ( file_exists( 'robots.txt' ) )
{
    $content = file_get_contents( 'robots.txt' );
}
else
{
    $content = '';
}
$content .= "\nSitemap: http://" . $_SERVER['HTTP_HOST'] . "/sitemaps/index";
// Set header settings
header( 'Content-Type: text/plain; charset=UTF-8' );
header( 'Content-Length: ' . strlen( $content ) );
header( 'X-Powered-By: eZ Publish' );

while ( @ob_end_clean() );

echo $content;

eZExecution::cleanExit();
?>