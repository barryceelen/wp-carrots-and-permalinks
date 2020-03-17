# 🐰 Carrots and Permalinks

The core WordPress "post" and "page" post types hold quite the coveted place when it comes to WordPress permalinks:

1. Post URLs are not prefixed with their post type slug (unless you insist they do) and their URL structure can be easily managed via the permalinks settings page. 
2. Pages are a free-for-all and can be given any permalink depending on their slug and parent/child relationship.

Sometimes a site does not need posts or pages at all, and those core post types can be put to better use. Your site might be a business directory, a recipe site or anything else but a blog.

The example code in this repository illustrates how to effectively rename and reuse the core "post" post type and turn it into something much more interesting (to bunnies).

![Admin Screen](https://raw.githubusercontent.com/barryceelen/wp-carrots-and-permalinks/master/screenshot.png)

## 🥕 How to use this example

You can use the `carrots.php` file in the root of this repository as a starting point for your own post type. To see it in action place the `carrots.php` file in your `/mu-plugins` directory. Start by replacing its post type labels to make it yours.
