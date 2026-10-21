=== dmbc-multi-user-editor ===
Contributors: sbetts
Tags: editor, collaboration, multi-user
Requires at least: 6.0
Requires PHP: 8.0
Stable tag: 0.1.0
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

Foundation for a WordPress editor that supports multiple simultaneous users.

== Description ==

This initial scaffold provides the plugin entry point, Composer dependency
management, and GitHub updates. Collaborative editing is not implemented yet.

== Installation ==

1. Place the dmbc-multi-user-editor folder in wp-content/plugins.
2. For a Git checkout, run composer install --no-dev --prefer-dist in that folder.
   Release ZIPs must already include the vendor directory.
3. Activate dmbc-multi-user-editor in the WordPress Plugins screen.

If dependencies are missing, an administrator notice explains how to install
them and the plugin does not initialize.

== Updates ==

Git origin: git@github.com:zagrev/dmbc-multi-user-editor.git
Update repository: https://github.com/zagrev/dmbc-multi-user-editor
Default branch: main

Updates are provided by yahnis-elsts/plugin-update-checker, installed through
Composer. No Git executable or SSH key is required on the WordPress server for
public repository updates.

To publish an update, keep the plugin header version and Stable tag in sync,
create a matching GitHub version tag/release (for example, v0.1.0), and attach
dmbc-multi-user-editor.zip. The ZIP must contain a top-level
dmbc-multi-user-editor folder with the plugin files and production vendor
dependencies installed using composer install --no-dev --prefer-dist
--optimize-autoloader.

Only the named release ZIP is accepted for updates. GitHub's generated source
archives omit vendor and are not suitable for installation. Versions without
the required release asset are not offered as updates.

== Development ==

Run composer install to install the locked dependencies.
Run composer validate --strict to validate the Composer configuration.
Run composer lint to check the plugin entry point's PHP syntax.
Commit composer.json and composer.lock, but not vendor.
The existing LICENSE file contains the Apache License 2.0 terms.

== Changelog ==

= 0.1.0 =
* Initial plugin scaffold with Composer-managed GitHub updates.
