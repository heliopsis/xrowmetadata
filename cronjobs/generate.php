<?php

if ( ! $isQuiet )
{
    $cli->output( "Generating Sitemap...\n" );
}   
// Get a reference to eZINI. append.php will be added automatically.
$ini = eZINI::instance( 'site.ini' );
$googlesitemapsINI = eZINI::instance( 'googlesitemaps.ini' );

// Settings variables
if ( $googlesitemapsINI->hasVariable( 'Classes', 'ClassFilterType' ) && $googlesitemapsINI->hasVariable( 'Classes', 'ClassFilterArray' ) && $ini->hasVariable( 'SiteSettings', 'SiteURL' ) )
{
    $classFilterType = $googlesitemapsINI->variable( 'Classes', 'ClassFilterType' );
    $classFilterArray = $googlesitemapsINI->variable( 'Classes', 'ClassFilterArray' );
}
else
{
    $cli->output( 'Missing INI Variables in configuration block GeneralSettings.' );
    return;
}

//getting custom set site access or default access
if ( $googlesitemapsINI->hasVariable( 'SiteAccessSettings', 'SiteAccessArray' ) )
{
    $siteAccessArray = $googlesitemapsINI->variable( 'SiteAccessSettings', 'SiteAccessArray' );
}
else
{
    $siteAccessArray = array( 
        $ini->variable( 'SiteSettings', 'DefaultAccess' ) 
    );
}

//fetching all language codes
$languages = array();

foreach ( $siteAccessArray as $siteAccess )
{
    $specificINI = eZINI::instance( 'site.ini.append.php', 'settings/siteaccess/' . $siteAccess );
    if ( $specificINI->hasVariable( 'RegionalSettings', 'Locale' ) )
    {
        array_push( $languages, array( 
            'siteaccess' => $siteAccess , 
            'locale' => $specificINI->variable( 'RegionalSettings', 'Locale' ) , 
            'siteurl' => $specificINI->variable( 'SiteSettings', 'SiteURL' ) 
        ) );
    }
}

foreach ( $languages as $language )
{
    if ( ! $isQuiet )
    {
        $cli->output( "Generating Sitemap for Siteaccess " . $language["siteaccess"] . " \n" );
    }
    $siteURL = $language['siteurl'];
    
    // Get the Sitemap's root node
    $rootNode = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ), $language['locale'] );
    
    if ( !$rootNode instanceof eZContentObjectTreeNode )
    {
        $cli->output( "Invalid SitemapRootNodeID in configuration block GeneralSettings.\n" );
        return;
    }
    
    require_once "access.php";
    $access = changeAccess( array( 
        "name" => $language["siteaccess"] , 
        "type" => EZ_ACCESS_TYPE_URI 
    ) );
    
    // Fetch the content tree
    $nodeArray = $rootNode->subTree( array( 
        'Language' => $language['locale'] , 
        'ClassFilterType' => $classFilterType , 
        'ClassFilterArray' => $classFilterArray 
    ) );
    

    $sitemap = new xrowGoogleSiteMap();

    // Generate Sitemap
    foreach ( $nodeArray as $subTreeNode )
    {
    	$object = $subTreeNode->object();
        $meta = xrowMetaDataFunctions::fetchByObject( $object );
        
        if ( $meta->googlemap != '0' )
        {
        	$url = 'http://' . $siteURL . '/' . $subTreeNode->attribute( 'url_alias' );
            $sitemap->add( $url, $object->attribute( 'modified' ), $meta->change, $meta->priority );
        }
    }
    // write XML Sitemap to file
    #$dir = eZSys::storageDirectory() . '/sitemap/';
    #mkdir( eZSys::storageDirectory() . '/sitemap', 0777, true );

    if ( count( $languages ) != 1 )
    {
        $filename =  xrowGoogleSiteMap::BASENAME . '_' . $language['siteaccess'] . '.' . xrowGoogleSiteMap::SUFFIX;
    }
    else
    {
    	$filename =  xrowGoogleSiteMap::BASENAME . '.' . xrowGoogleSiteMap::SUFFIX;
    }
    $sitemap->save( $filename );
    
    
    if ( ! $isQuiet )
    {
        $cli->output( "Sitemap $filename for siteaccess " . $language['siteaccess'] . " (language code " . $language['locale'] . ") has been generated!\n\n" );
    }
}
?>
