lop-cz/pexels-import
====================

Download and import photos from Pexels into Media Library.



Quick links: [Using](#using) | [Installing](#installing) | [Contributing](#contributing) | [Support](#support)

## Using

~~~
wp media pexels photo <id|page_url|random>... [--size=<name>] [--custom_size=<1920x1280>] [--crop] [--[no-]credit] [--title=<title>] [--caption=<caption>] [--alt=<alt_text>] [--desc=<description>] [--post_id=<post_id>] [--featured_image] [--porcelain]
~~~

**OPTIONS**

	<id|page_url|random>...
		One or more IDs or page URLs of the Pexels photo to import. Or pick 'random' from the Curated photos.

	[--size=<name>]
		Name of the predefined image size. Defaults to 'original'.
		---
		default: original
		options:
		  - original    # (full size)
		  - large2x     # (1880x1300)
		  - large       # (940x650)
		  - medium      # (?x350)
		  - small       # (?x130)
		  - portrait    # (800x1200 cropped)
		  - landscape   # (1200x627 cropped)
		  - tiny        # (280x200 cropped)
		---

	[--custom_size=<1920x1280>]
		Custom image size specified as maximum width and height.

	[--crop]
		Crop the image to the specified custom size.

	[--[no-]credit]
		Credit a photographer by inserting links to Pexels website in the Description field. Enabled by default if no description is provided. Single photo only.

	[--title=<title>]
		Attachment title (post title field). Single photo only.

	[--caption=<caption>]
		Caption for attachent (post excerpt field). Single photo only.

	[--alt=<alt_text>]
		Alt text for image (saved as post meta).

	[--desc=<description>]
		"Description" field (post content) of attachment post.

	[--post_id=<post_id>]
		ID of the post to attach the imported file to.

	[--featured_image]
		If set, set the imported image as the Featured Image of the post its attached to. Single photo only.

	[--porcelain]
		Output just the new attachment ID.

**EXAMPLES**

    # Import single photo by ID in 'large' size.
    $ wp media pexels photo 3604268 --size=large

    # Import single photo in custom size without link to Pexels in the description and set it as Featured image for the post ID 1.
    $ wp media pexels photo 3604268 --custom_size=1920x1280 --no-credit --featured_image --post_id=1

    # Import single photo by page URL in 'original' size with custom title and caption.
    $ wp media pexels photo https://www.pexels.com/photo/snow-covered-pine-trees-3604268/ --title="Snow trees" --caption="Winter is coming"

    # Import 2 random (Curated) photos cropped to custom size.
    $ wp media pexels photo random random --custom_size=960x960 --crop

    # Import 3 photos in 'medium' size with the Alt text and attach them to the post ID 1.
    $ wp media pexels photo 3143922 2703181 3534924 --size=medium --alt="City" --post_id=1

## Installing

Installing this package requires WP-CLI v2 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with:

    wp package install git@github.com:lop-cz/pexels-import.git

## Contributing

We appreciate you taking the initiative to contribute to this project.

Contributing isn’t limited to just code. We encourage you to contribute in the way that best fits your abilities, by writing tutorials, giving a demo at your local meetup, helping other users with their support questions, or revising our documentation.

For a more thorough introduction, [check out WP-CLI's guide to contributing](https://make.wordpress.org/cli/handbook/contributing/). This package follows those policy and guidelines.

### Reporting a bug

Think you’ve found a bug? We’d love for you to help us get it fixed.

Before you create a new issue, you should [search existing issues](https://github.com/lop-cz/pexels-import/issues?q=label%3Abug%20) to see if there’s an existing resolution to it, or if it’s already been fixed in a newer version.

Once you’ve done a bit of searching and discovered there isn’t an open or fixed issue for your bug, please [create a new issue](https://github.com/lop-cz/pexels-import/issues/new). Include as much detail as you can, and clear steps to reproduce if possible. For more guidance, [review our bug report documentation](https://make.wordpress.org/cli/handbook/bug-reports/).

### Creating a pull request

Want to contribute a new feature? Please first [open a new issue](https://github.com/lop-cz/pexels-import/issues/new) to discuss whether the feature is a good fit for the project.

Once you've decided to commit the time to seeing your pull request through, [please follow our guidelines for creating a pull request](https://make.wordpress.org/cli/handbook/pull-requests/) to make sure it's a pleasant experience. See "[Setting up](https://make.wordpress.org/cli/handbook/pull-requests/#setting-up)" for details specific to working on this package locally.

## Support

Github issues aren't for general support questions, but there are other venues you can try: https://wp-cli.org/#support


*This README.md is generated dynamically from the project's codebase using `wp scaffold package-readme` ([doc](https://github.com/wp-cli/scaffold-package-command#wp-scaffold-package-readme)). To suggest changes, please submit a pull request against the corresponding part of the codebase.*
