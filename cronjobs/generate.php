<?php
require_once "access.php";
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
if ( $googlesitemapsINI->hasVariable( 'SiteAccessSettings', 'AvailableSiteAccessList' ) )
{
    $siteAccessArray = $googlesitemapsINI->variable( 'SiteAccessSettings', 'AvailableSiteAccessList' );
}
else
{
    $siteAccessArray = array( 
        $ini->variable( 'SiteSettings', 'DefaultAccess' ) 
    );
}

//fetching all language codes
$languages = array();
$allDomains = array();
foreach ( $siteAccessArray as $siteAccess )
{
    $specificINI = eZINI::instance( 'site.ini.append.php', 'settings/siteaccess/' . $siteAccess, true, false, false, true );
    if ( $specificINI->hasVariable( 'RegionalSettings', 'Locale' ) )
    {
        array_push( $languages, array( 
            'siteaccess' => $siteAccess , 
            'locale' => $specificINI->variable( 'RegionalSettings', 'ContentObjectLocale' ) , 
            'siteurl' => $specificINI->variable( 'SiteSettings', 'SiteURL' ) 
        ) );
    $domain = preg_split( '/[\/\:]/i', $specificINI->variable( 'SiteSettings', 'SiteURL' ), 2 );
    if ( is_array( $domain ) )
    {                  
        $allDomains[] = $domain[0];             
    }
    else
    {
        $allDomains[] = $siteURL;
    } 
    }
}
$allDomains = array_unique( $allDomains );

foreach ( $languages as $language )
{
    if ( ! $isQuiet )
    {
        $cli->output( "Generating Sitemap for Siteaccess " . $language["siteaccess"] . " \n" );
    }
    
    $siteURL = $language['siteurl'];
    $domain = preg_split( '/[\/\:]/i', $siteURL, 2 );
    if ( is_array( $domain ) )
    {
        $domain = $domain[0];
    }
    else
    {
        $domain = $siteURL;
    }
    // Get the Sitemap's root node
    $rootNode = eZContentObjectTreeNode::fetch( eZINI::instance( 'content.ini' )->variable( 'NodeSettings', 'RootNode' ) );
    
    if ( ! $rootNode instanceof eZContentObjectTreeNode )
    {
        $cli->output( "Invalid RootNode.\n" );
        return;
    }
    
    /* Change the siteaccess */
    $access = changeAccess( array( 
        "name" => $language["siteaccess"] , 
        "type" => EZ_ACCESS_TYPE_URI 
    ) );
    unset( $GLOBALS['eZContentObjectDefaultLanguage'] );
    eZContentLanguage::expireCache();
    // Fetch the content tree
    $nodeArray = $rootNode->subTree( array( 
        'Language' => $language['locale'] , 
        'ClassFilterType' => $classFilterType , 
        'ClassFilterArray' => $classFilterArray 
    ) );
    
    $sitemap = new xrowGoogleSiteMap( );
    // Generate Sitemap
    foreach ( $nodeArray as $subTreeNode )
    {
        $object = $subTreeNode->object();
        $meta = xrowMetaDataFunctions::fetchByObject( $object );
        
        if ( $meta->googlemap != '0' )
        {
	    if ( count( $allDomains ) != 1 and in_array( $domain, $allDomains ) )
{
$url = 'http://' . $domain . '/' . $subTreeNode->attribute( 'url_alias' );
}
else
{
$url = 'http://' . $domain . '/' . $language["siteaccess"] . '/' . $subTreeNode->attribute( 'url_alias' );
}
            $sitemap->add( $url, $object->attribute( 'modified' ), $meta->change, $meta->priority );
        }
    }
    // write XML Sitemap to file
    $dir = eZSys::storageDirectory() . '/sitemap';
    mkdir( $dir, 0777, true );
    
    if ( count( $languages ) != 1 )
    {
        $filename = $dir . '/' . xrowGoogleSiteMap::BASENAME . '_' . $language['siteaccess'] . '.' . xrowGoogleSiteMap::SUFFIX;
    }
    else
    {
        $filename = $dir . '/' . xrowGoogleSiteMap::BASENAME . '.' . xrowGoogleSiteMap::SUFFIX;
    }
    $sitemap->save( $filename );
    
    if ( ! $isQuiet )
    {
        $cli->output( "Sitemap $filename for siteaccess " . $language['siteaccess'] . " (language code " . $language['locale'] . ") has been generated!\n\n" );
    }
}
?>
