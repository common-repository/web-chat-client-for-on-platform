=== Web Chat Client for ON Platform ===
Contributors: gameontechnology
Tags: onplatform, chatbot, ai
Requires at least: 4.7
Tested up to: 6.4.3
Stable tag: 1.0.2
Requires PHP: 7.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A plugin for embedding your ON Platform chatbot on your site.

== Description ==

With the ON Platform Web Chat plugin, you can embed and configure your ON Platform chatbot on your WordPress site.

Configure whether your chatbot opens automatically or not, how your chat button looks like, where on your site it's embedded, and more.

This plugin relies on ON Platform's chat infrastructure, and sends your user's chat queries to ON Platform's servers for processing a response. You can learn more about ON by visiting the [official website](https://www.onplatform.com/), reading their [terms of service](https://www.onplatform.com/terms-of-use), and their [privacy policy](https://www.onplatform.com/privacy-policy).

== Installation ==

Install and enable this plugin, then head over to the settings page to configure how you'd like your chatbot to be embedded on your site.

### Configuration options

- **Client ID (required):** The client ID for your chatbot. This should've been provided by our customer support team.
- **Auto Open Chat Window:** If checked, it the chatbot window will open automatically when your site loads on a device larger than a mobile phone.
- **Auto Open Chat Window on Mobile:** If checked, it the chatbot window will open automatically when your site loads on a mobile phone device.
- **Hide Widget Button:** If checked, the chat button will not display on your page. Only check this option if you plan to programatically open your chatbot using javascript.
- **Initial Chat Prompt:** If provided, the chatbot will initialize conversations with this prompt, rather than the default welcome message.
- **Open Image URL:** If you'd like to customize the image used for the "open chat" button, you can upload a file using this field.
- **Close Image URL:** If you'd like to customize the image used for the "close chat" button, you can upload a file using this field.
- **Display Widget:** You can choose between embedding your chatbot in all of your site's URLs, or specific URLs. You can use the `*` character as a wildcard in your path. For example, if you'd like to embed your chatbot under all pages within the "tickets" subroute, you can write a URL like `mysite.com/tickets/*`.

If you need help with any of the values in this form, you can email us at [support@onplatform.com).

== Frequently Asked Questions ==

= How do I get my Client ID? =

The Client ID should've been provided to you by our customer support team if you are a onplatform  partner. If you didn't receive your Client ID, please email us at [support@onplatform.com).

== Screenshots ==

1. The configuration panel.

== Changelog ==

= 1.0.0 =
* Our first release! Use this plugin to embed your ON Platform chatbot on your WordPress site.

= 1.0.1 =
* Updated plugin metadata

= 1.0.2 =
* Updated plugin metadata

== Upgrade Notice ==

= 1.0.0 =
* Our first release.
