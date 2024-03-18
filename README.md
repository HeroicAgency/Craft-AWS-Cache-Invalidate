# AWS CloudFront Cache Invalidator

The ultimate Craft 4.3.5+ / 5+ plugin to invalidate the AWS CloudFront cache.


## Notes

* When saving an entry with a url, it will invalidate just that url.
* If you save a category or global, it will trigger a global invalidation.  This is because those elements can be used systemically.
* You can disable the plugin in specified environments -- store AWS settings in config or .ENV files, not force you to provide a region, and a button in the control panel utilites to manually invalidate.
* Add a new section in Utilities called "Invalidate AWS Cache".  Here you can manually trigger a global invalidation, or just a specific page.
* You can also trigger a global invalidation by hitting this url: /cache-invalidator/invalidate
  * ex: https://yoursite.com/cache-invalidator/invalidate
  * Great for cron jobs
* Through either the settings panel or your .ENV variables, you can specify:
  * Enable/Disable the plugin (great for disabling locally but leaving in production).
  * Access Key / Secret Acces key IDs (which you can also leave empty if you are using an EC2 instance with an applicable IAM role assignment)
  * Region (Or leave blank because CloudFront does not require it in some cases).
  * Distribution ID

## Requirements

This plugin requires Craft CMS 4.3.5 or later, and PHP 8.0.2 or later.

## Installation

You can install this plugin from the Plugin Store or with Composer.

#### From the Plugin Store

Go to the Plugin Store in your project’s Control Panel and search for “AWS CloudFront Cache Invalidator”. Then press “Install”.


#### With Composer

Open your terminal and run the following commands:


```bash
# go to the project directory
cd /path/to/my-project.test

# tell Composer to load the plugin
composer require heroic/craft-cache-invalidator

# tell Craft to install the plugin
./craft plugin/install cache-invalidator
```
