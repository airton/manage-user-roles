=== Manage User Roles ===
Contributors: airtonvancin
Donate link: https://www.mercadopago.com.br/checkout/v1/redirect?pref_id=93975544-d3277b00-b729-47a7-bfa0-9a19d4e5afec
Tags: user, roles, administration, content visibility, permissions
Requires at least: 3.0
Tested up to: 6.9
Stable tag: 2.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A flexible plugin to control content visibility for non-administrator users with advanced, role-based rules.

== Description ==

This plugin gives administrators fine-grained control over content visibility in the WordPress admin area. Instead of a one-size-fits-all approach, you can set specific viewing permissions for each user role.

**Core Features:**

*   **Role-Based Permissions:** For each user role (like Editor, Author, Contributor), you can decide what content they are allowed to see.
*   **Flexible Rules:** Choose between two simple but powerful rules for each role:
    *   **See only their own content:** The user will only see the posts, pages, or custom post types they have personally created.
    *   **See all content:** The user will have no content restrictions and can see everything, just like an administrator.
*   **Admin Bar Cleanup:** The "Edit" link on the admin bar is automatically hidden when a user is viewing a post they don't have permission to see.
*   **Administrator Override:** Administrators are never affected by these rules and can always see all content.

This is the perfect tool for multi-author websites, magazines, or any project where you need to ensure users only have access to the content relevant to them.

== Installation ==

1.  Upload the `manage-user-roles` folder to the `/wp-content/plugins/` directory.
2.  Activate the plugin through the 'Plugins' menu in WordPress.
3.  Navigate to **Settings > Manage User Roles** in your WordPress admin panel.
4.  For each user role listed, select the desired viewing rule from the dropdown menu.
5.  Click "Save Changes". The rules will be applied immediately.

== Frequently Asked Questions ==

= How do I configure the plugin? =

Go to **Settings > Manage User Roles**. There you will find a list of all user roles on your site (except for Administrator). For each role, you can choose whether they see "only their own content" or "all content".

= What is the default setting? =

By default, all non-administrator roles are set to "See only their own content" for maximum security. You can change this at any time.

= Can I set more complex rules, like allowing one role to see another's posts? =

This version of the plugin supports rules for seeing one's own content or all content. More complex rules are being considered for future versions.

== Screenshots ==

1. Configurações do Plugin: Defina as permissões de visualização para cada função de usuário.

== Changelog ==

= 2.0.0 =
* **Major Overhaul:** Replaced the simple on/off switch with a flexible, role-based settings page.
* Administrators can now set viewing permissions ("own content" or "all content") for each user role individually.
* Rewrote the core filtering logic to be more efficient and accommodate the new rules.
* Updated and improved the plugin documentation.

= 1.2.0 =
* Add a settings page to allow administrators to enable or disable the functionality.
* Improve plugin description and instructions.

= 1.1.0 =
* Add Portuguese (Brazil) and Spanish language support.

= 1.0.0 =
* Initial version.

== License ==

This file is part of Manage User Roles.

Manage User Roles is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published
by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

Manage User Roles is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

Get a copy of the GNU General Public License in <http://www.gnu.org/licenses/>.
