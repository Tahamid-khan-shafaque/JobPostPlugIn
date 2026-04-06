=== Job Post Manager ===
Contributors: tahamidkhan
Tags: jobs, job board, job posting
Requires at least: 5.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Simple job posting plugin to display job cards, filtering options on sidebar, and inline single job views cleanly within the shortcode page.

== Description ==
Job Post Manager is a lightweight yet fully-functional WordPress plugin designed to allow site owners to easily create, manage, and display job postings. Use a simple shortcode `[job_board]` to display a modern job layout complete with search and filtering features in a sidebar. 

A key feature is that clicking "Read More" securely loads the entire job description fully inside the same page via the shortcode itself, bypassing your theme's default single post templates for a dedicated, unified design!

Features:
* Custom post type for Job Postings.
* Job Categories and Job Types taxonomies.
* Beautiful responsive card design for job listings.
* Inline Single Job Viewer - read job details fully right on the page you host the shortcode on.
* Sidebar AJAX filtering functionality (Category, Type, Keyword search).
* Built-in pagination.
* Fully secure and customizable.

== Installation ==
1. Upload the `job-plugin` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Add job postings via the "Job Posts" menu in the admin dashboard.
4. Place the shortcode `[job_board]` on any page or post to display the job listings.

== Frequently Asked Questions ==
= Does this require a paid upgrade? =
No, everything in this plugin works without any upgrades or premium versions.

= How do I display the jobs? =
Simply create a page and put the shortcode `[job_board]` in it.

= Where do the single jobs load? =
Instead of loading onto a disconnected single standard WordPress layout, jobs load flawlessly inline on the exact same page where the `[job_board]` shortcode is placed.

== Screenshots ==
1. The frontend job board layout with AJAX sidebar filters.
2. The inline expanded single job viewing experience.
3. The backend job posting editor.

== Changelog ==
= 1.0 =
* Initial release including CPT, Taxonomy, Shortcode, Sidebar filtering, and unified single job rendering.
