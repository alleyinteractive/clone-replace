=== Plugin Name ===
Contributors: mboynes, alleyinteractive
Tags: editing, duplication, replacement, workflow
Requires at least: 3.5
Tested up to: 3.6
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Gives you the ability to clone posts, and replace posts. Together, you have a very powerful tool for a fork/merge editing model.

== Description ==

WordPress has a wonderful editing model with its "draft workflow", where writers can save unpublished drafts, preview posts, submit drafts for review from a superior, and even schedule a post's publication. This is all that most writers and teams ever needed for a blog. WordPress has extended beyond blogs, and has grown to be the preferred platform for any person or team writing for the web. While WordPress has evolved, its editing model has not, and this editing model is limited. Once a post is published, edits cannot follow the same workflow. That's where this plugin comes in. This plugin has two powerful features, the ability to clone posts and the ability to replace posts with another, which combined, allow writers to leverage core's "draft workflow" for published posts.

= Features =

* *Create clones of existing posts.* The replicas are created as drafts, and have all the same data (including terms and post meta) as the original post. The only things not cloned are child posts (which include revisions, attachments, and other posts)
* *Replace one post with another.* The replaced post becomes a revision of itself, the replacing post is essentially deleted, and you're left with the replacing post's data with the post ID and slug of the replaced post.

Combined, these features give writers a pseudo-"fork and merge" model. Published posts can be cloned, the clones edited as any other draft can be, then the originals replaced with the clones.

Individually, these features are equally as useful. You don't need to use the "fork and merge" model to take advantage of this plugin.


== Installation ==

1. Upload `clone-replace` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= If I clone post A into post B, then make edits to post A, then replace it with post B, are those edits retained and merged into the final result? =

No, those edits are overridden. For a more typical fork & merge writing model, check out [Post Forking](http://wordpress.org/plugins/post-forking/)

= Can I schedule a post to be replaced? =

Absolutely! Post replacement happens when the post is published, so you can replace a post in the same ways you can publish any other post.

= The "Replace" fields don't appear on published posts. What gives? =

This is done as a fail-safe. At this time, replacement happens on *publish*. Remember that the replacing post will be deleted after it is merged into the to-be-replaced post. If your replacing post is published, then you will be deleting a published post, leaving a 404 on your site. If you want to replace a post with a published post, you must first unpublish it.

= What will/won't be cloned/replaced? =

Everything about a post will be cloned except *child posts* (which includes attachments and revisions) and some core post meta, like _edit_lock and _edit_last (though you can alter the post meta that's skipped with a filter)

Everything about a post will be replaced except its *Post ID*, *Slug*, *GUID*, *Post Status*, and *child posts* (including attachments and revisions).


== Screenshots ==



== Upgrade Notice ==

= 0.1 =
Brand new, nothing to see here.

