Installation:

After this the extension has been installed. You should be able to generate the sitemap 
using the runcronjobs.php script. See "googlesitemaps.ini" for more configuration options.

    # php runcronjobs.php googlesitemaps
    Running cronjob part 'googlesitemaps'
    Running extension/xrowmetadata/cronjobs/generate.php
    Generating Sitemap...

    Sitemap has been generated!

This will create a file for every siteaccess within your eZ Publish root directory. These files are usually named "sitemap_access.xml", but you can change that in the INI file.

Please ensure that your Apache rewrite rules permit access to the XML File. This can be done by adding the following line to your .htaccess or Apache configuration file:

    RewriteRule ^sitemap[^/]*\.xml - [L]
    
Troubleshooting & support
=========================
Send email to service [at] xrow [dot] de


