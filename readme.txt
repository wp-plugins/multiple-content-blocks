=== Plugin Name ===
Contributors: Ontwerpstudio Trendwerk, Harold Angenent
Donate link: http://plugins.trendwerk.nl
Tags: multiple,content,blocks,multiplecontent,page,pageblocks
Requires at least: 2.8
Tested up to: 2.8.6
Stable tag: 1.0

Lets you use more than one content "block" on a template. You only have to insert one tag inside the template, so it's easy to use.

== Description ==

With this plug-in, you can use more than one content &quot;block&quot; on a template. You only have to insert one tag inside the template, so it’s easy to use.

I made this plug-in because I think it is essential to any CMS to be able to use more content blocks in one template. I really missed this functionality in Wordpress and I did not find a decent plug-in for this, so I made one myself.

<strong>What is a &quot;multiple content block&quot;?</strong>

When you make a Wordpress template, you can show the content of the current page by using the code `the_content();`, but when you have (for example) several columns, you cannot split these in different content &quot;blocks&quot;.

You want your clients to edit the content themselves without screwing up any code. This is where our plug-in comes in.



If you have any questions or tips, please contact me at harold@trendwerk.nl or leave  a comment on the blog.


== Installation ==

1. Extract the contents to the `/wp-content/plugins/multiple-content/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Place `<?php the_block('blockname'); ?>` in your template where you want a content block
4. Edit this content on the page editing page

== Frequently Asked Questions ==

= How do I filter the content? =

You can set the second parameter to false, like this: `<?php $content_to_edit = the_block('blockname',false); ?>` and you can now edit this variable with PHP.


== Screenshots ==

1. How to use
2. The edit page will get the editors

== Changelog ==

= 1.0 =
* Plugin created


== A brief Markdown Example ==

Ordered list:

1. Create your own content blocks in a template
2. Fill them with a WYSIWYG Editor

Unordered list:

* Create your own content blocks in a template
* Fill them with a WYSIWYG Editor