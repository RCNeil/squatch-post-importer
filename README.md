# squatch-post-importer
Used in conjunction with squatch-post-exporter, a simple Wordpress importer that creates posts from a CSV

## Overview

Squatch Post Importer allows you to import posts from a CSV file into WordPress. You can:

- Choose a CSV from your Media Library  
- Map posts to any public post type  
- Optionally perform a **string replace** on post content (requires old and new URLs to be provided)  
- Attach featured images by matching filenames in the Media Library  
- Map custom post meta and SEO fields  

The plugin provides a progress bar and logs details of imported posts, including author, post ID, permalink, featured image, SEO, and custom fields.

---

## Features

- Select CSV from Media Library  
- Optional string replace for URLs in post content  
- Choose target post type for import  
- Automatic assignment of authors (by username or email)  
- Map featured images automatically (matching filenames in uploads)  
- Placeholder support for custom post meta (ACF or other fields)  
- Placeholder support for Yoast SEO metadata  
- Batch import with progress tracking  

---

## Requirements / Prerequisites

⚠️ Before importing posts, ensure:

1. **Media Library must be copied over** with the same filenames and paths as in the CSV. This is required so the plugin can match featured images correctly.  
2. **Authors must already exist** in the site with the correct username or email. Otherwise, posts will default to the currently logged-in user.  
3. **Custom post meta and SEO mappings** must be updated in the plugin code to match your CSV headers and meta keys.
4. The string replace feature will **only work if both Old URL and New URL are provided**. Leaving them blank will skip the replacement.
5. **ALWAYS** make a backup before using the importer. Use at your own risk. 

---

## Installation

1. Upload the `squatch-post-importer` folder to your WordPress `/wp-content/plugins/` directory.  
2. Activate the plugin via the **Plugins** menu in WordPress.  
3. Navigate to **Tools → Squatch Post Importer** to access the importer interface.  

---

## Usage

1. Click **Select CSV** to choose your CSV file from the Media Library.  
2. Optionally, fill in the **Old URL** and **New URL** fields for string replacement in post content.  
3. Select the **Post Type** for import.  
4. Click **Start Sync**.  
5. Watch the progress bar and output log for import status.  

---

## Customization

### Custom Post Meta

Update the `$custom_map` array in the plugin code to map CSV headers to meta keys:

```php
$custom_map = [
   'CSV Header 1' => 'meta_key_1',
   'CSV Header 2' => 'meta_key_2',
];
```

### Yoast SEO

Update the `$yoast_map` array in the plugin code to map CSV headers to meta keys:

```php
$yoast_map = [
  '_yoast_wpseo_title'      			  => '_yoast_wpseo_title',
  '_yoast_wpseo_metadesc'				  => '_yoast_wpseo_metadesc',
  '_yoast_wpseo_focusk'   			  => '_yoast_wpseo_focuskw',
  '_yoast_wpseo_canonical' 			  => '_yoast_wpseo_canonical',
  '_yoast_wpseo_meta-robots-noindex'    => '_yoast_wpseo_meta-robots-noindex',
  '_yoast_wpseo_meta-robots-nofollow'   => '_yoast_wpseo_meta-robots-nofollow',
];
```

##Notes

- The plugin uses AJAX and batch processing to handle large CSV imports.
- Featured images are attached by matching filenames from the uploads folder.
- Posts with missing authors will default to the currently logged-in admin user.
- The plugin outputs a summary for each post: title, ID, permalink, author, date, featured image, SEO update, and custom meta update.

