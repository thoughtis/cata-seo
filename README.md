# Cata SEO

Adds features related to search indexing and structured data. Intended for Thought & Expression Co. sites — the Organization schema includes company details.

## Features

### Structured Data

Outputs JSON-LD structured data in the footer for Google rich results:

- **Organization** schema on the front page: name, logo, parent company, founders, address and contact info. It no longer claims the parent's legal name as each site's alternate name or guesses a founding date from the oldest post; sites add verified facts with `cata_seo_organization_data`.
- **WebSite** schema on the front page, linked to the Organization by `@id`.
- **Article / NewsArticle** schema on single posts (`NewsArticle` for the `news` category): headline, UTC-offset dates, publisher, authors via Co-Authors Plus, and, when there is a featured image, images in the three ratios Google requires (1:1, 4:3, 16:9), registered as custom image sizes.
- **BreadcrumbList** schema built from crumbs provided by the `cata_breadcrumbs_get_crumbs` filter.

### Robots

Applies `noindex, follow` via the `wp_robots` filter to:

- Attachment pages
- Paged content (`/page/2/` etc.)
- Requests with query strings
- Search results
- 404 pages

Also adds `Disallow: */feed/` to `robots.txt`.

### Jetpack Settings

- Enables Jetpack Open Graph tags.
- Article meta, Open Graph and Twitter descriptions end on a whole sentence: up to 160 characters, extended to 300 when the first sentence needs it. Jetpack's source wins (custom SEO copy, then a manual excerpt, then the body), and image and gallery captions are skipped. Other pages keep the 150-character word cut.
- News sitemap titles are plain text before Jetpack's XML writer escapes them, and stories older than 48 hours leave the news sitemap.
- Publishing a post queues one Jetpack sitemap rebuild five minutes later, so `sitemap.xml` doesn't wait for Jetpack's 12-hour schedule.
- Excludes images from the Jetpack image sitemap when they are unattached or attached to a reusable block.

### Share Image

Adds a "Share Image" panel to the Customizer for setting a homepage share image (2:1 ratio recommended). When the front page shows latest posts, the image is used for Open Graph and Twitter Card tags.

## Requirements

- Jetpack (for Open Graph, sitemap, and share image features)
- Co-Authors Plus (optional, for author structured data)

## Filters

| Filter | Purpose |
| --- | --- |
| `cata_structured_data_array` | Modify the full structured data output |
| `cata_breadcrumbs_get_crumbs` | Provide breadcrumb crumbs (`title`, `url`) |
| `cata_seo_organization_data` | Modify the front-page Organization node (add verified facts such as `foundingDate`) |
| `cata_seo_website_schema` | Return false to drop the front-page WebSite node (default true) |
| `cata_seo_article_requires_image` | Return true to skip Article data for posts without a featured image (default false) |
| `cata_seo_sentence_descriptions` | Return false to keep Jetpack's own description for a post (default true) |
| `cata_seo_decode_news_titles` | Return false to leave news sitemap titles as Jetpack builds them (default true) |
| `cata_seo_news_sitemap_window` | Seconds a story stays in the news sitemap (default two days) |
| `cata_seo_rebuild_sitemaps_after_publish` | Return false to skip the sitemap rebuild after a post publishes (default true) |

Portrait share-image crops are owned by thoughtnet's `cata-social` plugin (`Share_Image::get_portrait_image()`); this plugin no longer crops `og:image`.
