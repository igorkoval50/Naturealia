# 1.1.14
- Fix: admin / assigned products / correct labels for variants
- Fix: admin / global search works again in SW 6.4

# 1.1.13
- Fix: SW 6.4 - correct uninstallation 

# 1.1.12
- Change: variant product assignment: if assigned to main product, blog posts will also show on variants
- Version: Support for SW 6.4

# 1.1.11
- Change: CMS listing filter (categories, tags, authors)
- Fix: correct sorting of shopware custom fields
- Fix: admin / blog category / cms page: list is now sorted
- Fix: cms element "blog products": removed layout mode "standard" (gave wrong layout)

# 1.1.10
- Fix: add blog sorting to rss feed
- Change: support for netzpShariff 1.0.2

# 1.1.9
- Change: optionally prevent blog posts from indexing 
- Change: shopping experience block "blog listing": added custom layout / twig templating
- Fix: rss feed respects the sales channel
- Fix: correct seourl on paginated blog lists
- Fix: better responsive layout for listing layout "list"

# 1.1.8
- Change: include in rss feed via category setting

# 1.1.7
- Change: multiple categories for blog posts
- Change: restrict blog category to sales channel
- Change: restrict blog category to customer group
- Change: restrict blog post to logged in users
- Fix: add meta (title/description, also for facebook and twitter) to custom layout blog posts

# 1.1.6
- Fix: not using scss relative paths anymore (./psh.phar storefront:hot-proxy has produced errors)
- Fix: variant products assignable to blog posts
- Fix: blog detail / proper url generation 
- Change: gallery is included in default blog layout

# 1.1.5
- Fix: display of blogposts in articles respects the time schedule

# 1.1.4
- Change: media gallery - support for documents / downloads + full image size zoom / thumbnail captions
- Change: open graph / twitter tags modified (blog title, description, image)

# 1.1.3
- Fix: correct path to node_modules (netzp-blog-gallery.plugin.js)

# 1.1.2
- Added: blogpost edit: tab "items" for recipes, guides, reviews etc.
- Added: blogpost edit: tab "image gallery"
- Added: support for standard shopware custom fields (attention: this is still a bit buggy in shopware overall)
- Change: blogpost edit: "images/media" moved to own tab
- Change: shopping experience blocks moved to own section 
- Change: shopping experience / blog detail element: product slider configurable (layout, display mode, minimum width)

# 1.1.1
- Fix: update / migration sometimes failed

# 1.1.0
- Change: select cms page / free blog layout (globally and/or per category)
- Change: smaller layout changes in backend
- Change: support for Shopware SEO Templates 
- Change: blog post can have an author (optional) / filtering by author in shopping experience
- Change: shopware pagination mechanism is being used
- Change: RSS feed (/blog.rss)
- Change: support for rich snippets
- Change: revised image thumbnail sizes
- Important: please clear ALL caches and regenerate the SEO index after update to 1.1.0!

# 1.0.15
- Fix: sitemap is built correctly when having more than 100 posts

# 1.0.14
- Change: additional twig blogs (element/blog-index* and page/blog/post*)

# 1.0.13
- Change: additional custom field (free to use in own templates: post.translated.custom)
- Change: support for tags

# 1.0.12
- Fix: sitemap generation works again

# 1.0.11
- Change: Limit search results in live search (only with our plugin Search Advanced)

# 1.0.10
- Change: search in administration
- Change: Filter by category in blog list

# 1.0.9
- Change: Support for our plugin "Search Advanced"
- Change: use thumbnails for preview images
- Change: optional preview image

# 1.0.8
- Fix: support for SW 6.2.2

# 1.0.7
- Change: product tab is only displayed if there are blog posts
- Change: blog list/layout cards: show only 2 columns on tablets
- Fix: product assignment: only the main products are displayed
- Fix: small layout fixes (blog list)

# 1.0.6
- Change: moved "blog categories" from settings/shop to settings/plugins
- Change: hide pagination when there's nothing to paginate ;-)
- Change: pagination: jump to blog content after page change
- Change: support for social sharing (with our plugin NetzpShariff)
- Fix: display problems in list view fixed

# 1.0.5
- Change: show related blog posts in product detail (optional)
- Change: paging in blog lists (set numberOfPosts > 0 in element config)
- Change: better image selection in blog editing

# 1.0.4
- Change: CMS blog element: better layout + background color option
- Change: CMS blog element: slider layout

# 1.0.3
- Fix: prices are shown on assigned products

# 1.0.2
- Change: Image layout: image size can be changed (contain, cover, auto)

# 1.0.1
- Change: Block and element for shopping experiences (URL /blog not working anymore)
- Change: Different layouts for index and detail view
- Change: Products can be assigned to blog posts
- Change: Blog post can be assigned to categories (defined in shop settings / blog categories)
- Change: Added twig blocks in templates

# 1.0.0
- initial version
