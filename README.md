# Cata SEO

Adds features related to search indexing and structured data. Intended for Thought & Expression Co. sites — the Organization schema includes company details.

## Features

### Structured Data

Outputs JSON-LD structured data in the footer for Google rich results:

- **Organization** schema on the front page: founding date (oldest post), founders, address, and contact info.
- **Article / NewsArticle** schema on single posts with a featured image (`NewsArticle` for the `news` category): headline, dates, publisher, authors via Co-Authors Plus, and images in the three ratios Google requires (1:1, 4:3, 16:9), registered as custom image sizes.
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
- Truncates meta descriptions to 150 characters at a word boundary.
- Excludes images from the Jetpack image sitemap when they are unattached or attached to a reusable block.

### Share Image

Adds a "Share Image" panel to the Customizer for setting a homepage share image (2:1 ratio recommended). When the front page shows latest posts, the image is used for Open Graph and Twitter Card tags.

### Portrait Share Image

Jetpack center-crops singular `og:image` URLs to 2:1, which cuts faces out of portrait photos. This re-crops portrait and square featured images to a band starting near the top of the image, where faces usually are.

## Requirements

- Jetpack (for Open Graph, sitemap, and share image features)
- Co-Authors Plus (optional, for author structured data)

## Filters

| Filter | Purpose |
| --- | --- |
| `cata_structured_data_array` | Modify the full structured data output |
| `cata_breadcrumbs_get_crumbs` | Provide breadcrumb crumbs (`title`, `url`) |
| `cata_seo_portrait_share_image_offset` | Percent of image height above the portrait share crop (default 12) |
