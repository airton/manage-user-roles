=== Manage User Roles ===
Contributors: airtonvancin
Donate link: https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=93975544-d3277b00-b729-47a7-bfa0-9a19d4e5afec
Tags: user, roles, administration, adm, internationalization, i18n, translation, portuguese, spanish
Requires at least: 3.0
Tested up to: 6.0
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A simple but powerful plugin to control content visibility for non-administrator users, ensuring they only see their own posts.

== Description ==

This plugin provides two main features to enhance user role management in the WordPress admin area:

1.  **Post Restriction:** For any user who is not an administrator, the posts list table (Posts, Pages, and any other post types) will be filtered to show only the content they have authored.
2.  **Admin Bar Cleanup:** It removes the "Edit" link from the admin bar when a non-administrator is viewing a single post they did not create.

This is perfect for multi-author sites where you want to prevent editors, authors, or contributors from seeing or accessing content created by other users. By default, these features are active, but they can be disabled by an administrator via the settings page.

== Installation ==

1.  Upload the `manage-user-roles` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  (Optional) Navigate to **Settings > Manage User Roles** in your WordPress admin panel.
4.  There you can uncheck the box to disable the plugin's restrictions if needed.

== Frequently Asked Questions ==

= What exactly does this plugin do? =

It filters the post list in the admin dashboard so that non-admin users can only see the posts they have created. It also hides the "Edit" button on the admin bar for posts they don't own.

= How can I disable this feature? =

If you are an administrator, you can go to **Settings > Manage User Roles** and uncheck the "Ativar Restrições" box. This will disable all plugin functionality.

= What languages are supported? =

* The plugin supports English (default), Portuguese (Brazil), and Spanish.

= What is the plugin license? =

* This plugin is released under a GPL license.

== Changelog ==

= 1.2.0 =
* Add a settings page to allow administrators to enable or disable the functionality.
* Improve plugin description and instructions.

= 1.1.0 =
* Add Portuguese (Brazil) and Spanish language support.
* Remove unused files and code.
* Update plugin version.

= 1.0.0 =

* Initial version.

== License ==

This file is part of Manage User Roles.

Manage User Roles is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published
by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Manage User Roles is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

Get a copy of the GNU General Public License in <http://www.gnu.org/licenses/>.
