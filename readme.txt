=== Search Merchandising - Track & Manage WooCommerce Product Search ===
Contributors: ninetyninew, freemius
Tags: product search, woocommerce search, woocommerce product search, search, woocommerce
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 7.0
Stable tag: 1.0.4
License: GNU General Public License v3.0
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Track and manage product search in your WooCommerce store. Track search terms, products clicked, add search term specific content, boost products and redirect WooCommerce search terms.

== Description ==

**Every day customers use product search on your WooCommerce store to find products. Wouldn’t it be great to know which search terms they use, the number of searches they conduct, the number of search results they receive and more?**

With Search Merchandising you can enhance WooCommerce search, the marketing within search results and increase conversions. Learn what your customers are searching for in product search results. 

The plugin builds a list of search terms customers have searched for and you can display specific marketing or informative content, boost specific products or even redirect search terms to help your customers find what they’re looking for more efficiently. Search Merchandising gives you full visibility and control over product searches on your WooCommerce website.

= Great features for marketing and conversions =

- **Track product search terms** including search term, total searches, and average results
- **Track products clicked** from specific search terms
- **Create in-search marketing content** to be displayed for specific search terms
- **Boost products in search results** for specific terms
- **Redirect specific search terms** to other pages
- Filter terms/products by date range and by search terms
- Disable tracking for specific user roles (e.g. disable tracking for searches by shop managers)

= Full search term visibility =

The terms tab lists the terms used in WooCommerce search to find products on your website.

Within the terms tab you can view:

- Term searched
- Number of searches
- Average results
- Products clicked from term search

You can choose the following options on specific terms:

- Content: Add any marketing content managed through the WordPress block editor to before/after search results
- Boosts: Select specific products to appear higher in search results, great for increasing conversions
- Redirect: Redirect a specific search term to another URL

You'll use the WordPress block editor to create any content you wish to include in WooCommerce search results. Once you've created your content, you can save it. When you return to the dashboard, you can then assign it to appear as before and/or after content in WooCommerce search.

= View products found from products search =

The products tab lists all the products that users have clicked on while searching for products on your website. Within the products tab you can see:

- Product clicked
- Number of product search clicks
- Search terms used to lead to a product search click

= Premium features =

This plugin has an optional premium plan which offers enhanced WooCommerce search and marketing features such as insights, dashboard widgets, hiding license/account information and priority support. By purchasing a premium plan you are contributing towards this plugin's future development. For further details view the upgrade page through the plugin's dashboard menu.

== Installation ==

= Minimum requirements =

* WordPress 5.0 or greater
* PHP 7.0 or greater
* WooCommerce must be installed and activated, we recommend using the latest version

= Automatic installation =

To do an automatic install of this plugin log in to your WordPress dashboard, navigate to the Plugins menu, and click "Add New".
 
Search for "Search Merchandising" and click the install button, once done simply activate the plugin.

= Manual installation =

Manual installation method requires downloading the plugin and uploading it to your web server via an FTP application. See these [instructions on how to do this](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).

= Getting started =

Once you have installed and activated the plugin go to Search Merch from the left menu in your WordPress dashboard, this is your Search Merchandising dashboard.

On first installation the dashboard will show no data, as customers search for products on your website the dashboard will be updated.

== Screenshots ==

1. View product search terms used
2. View products clicked from product search terms
3. Filter terms/products by dates/search
4. Viewing all in-search content created
5. Creating new in-search content to be used on specific search terms
6. In-search content displayed when a customer searches for "hoodie"
7. Settings overview

== Frequently Asked Questions ==

= Why aren't search terms being tracked?  =

Search Merchandising tracks search terms based off the standard WooCommerce product search results URL (e.g. https://yourwebsite.com/?s=search+term&post_type=product). Most WooCommerce websites keep this URL but your theme or other plugins may change it. If your search results URL does not include s=xxx and post_type=product then your search terms will not be tracked. If this is happening on your website ask your web/theme developer to revert the search results URL to be in the standard WooCommerce format to allow Search Merchandising to track search terms.

== Changelog ==

= 1.0.4 - 2021-03-21 =

* Changed: No data yet information with details on standard WooCommerce product search results URL
* Changed: Upgrade class now populates options on install/upgrade
* Changed: Dashboard colors so consistent with admin color changes in WordPress 5.7
* Changed: WordPress tested up to 5.7 declaration
* Changed: Screen resolution notice text
* Fixed: Various issues causing PHP 7 based deprecation notices when debug mode enabled
* Fixed: Various PHP 8 based notices/errors, note that the recommended PHP version for this extension remains 7.x
* Removed: Option population on activation as done via upgrade class on install/upgrade

= 1.0.3 - 2021-03-06 =

* Changed: License information conditional display amended
* Changed: Freemius SDK upgrade to 2.4.2

= 1.0.2 - 2021-01-18 =

* Fixed: Activating while WooCommerce disabled displays no permissions message
* Fixed: Upgrade button is displayed and links to restricted page if free and premium installed but only free active

= 1.0.1 - 2021-01-02 =

* Fixed: If WooCommerce is not active the notice appears within dashboard for users who cannot install plugins (e.g. subscribers)

= 1.0.0 - 2020-12-17 =

* Initial release