=== Very Simple Contact Form ===
Contributors: Guido07111975
Version: 12.3
License: GNU General Public License v3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
Requires PHP: 5.6
Requires at least: 4.7
Tested up to: 6.0
Stable tag: 12.3
Tags: simple, contact, form, contact form, email


This is a lightweight plugin to create a customized contact form. Add shortcode [contact] on a page or use the widget to display your form.


== Description ==
= About =
This is a lightweight plugin to create a customized contact form.

Add shortcode [contact] on a page or use the widget to display your form.

Form has fields for Name, Email, Subject and Message. It also has a sum to solve and a privacy consent checkbox.

You can personalize your form via the settingspage or by adding attributes to the shortcode or the widget.

It's also possible to list form submissions in your dashboard.

= How to use =
After installation add shortcode [contact] on a page or use the widget to display your form.

= Settingspage =
You can personalize your form via the settingspage. This page can be found via Settings > VSCF.

Settings and labels can be overridden when using the relevant (shortcode) attributes below.

This can be useful when having multiple contact forms on your website.

= Shortcode attributes =
You can also personalize your form by adding attributes to the shortcode mentioned above.

Misc:

* Add custom CSS class to form: `class="your-class-here"`
* Change default email address: `email_to="your-email-here"`
* Send to multiple email addresses (max 5): `email_to="first-email-here, second-email-here"`
* Change "From" email header: `from_header="your-email-here"`
* Change prefix of email subject: `prefix_subject="your prefix here"`
* Change email subject (including prefix): `subject="your subject here"`

Field labels:

* Change name label: `label_name="your label here"`
* Change email label: `label_email="your label here"`
* Change subject label: `label_subject="your label here"`
* Change message label: `label_message="your label here"`
* Change privacy consent label: `label_privacy="your label here"`
* Change submit label: `label_submit="your label here"`

Field error labels:

* Change name error label: `error_name="your label here"`
* Change email error label: `error_email="your label here"`
* Change subject error label: `error_subject="your label here"`
* Change sum error label: `error_sum="your label here"`
* Change message error label: `error_message="your label here"`
* Change links error label: `error_links="your label here"`

Form messages:

* Change message when sending fails: `message_error="your message here"`
* Change message when sending succeeds: `message_success="your message here"`
* Change message in confirmation email to sender: `auto_reply_message="your message here"`

Examples:

* One attribute: `[contact email_to="your-email-here"]`
* Multiple attributes: `[contact email_to="your-email-here" subject="your subject here" label_submit="your label here"]`

= Widget attributes =
The widget supports the same attributes. Don't add the main shortcode tag or the brackets.

Examples:

* One attribute: `email_to="your-email-here"`
* Multiple attributes: `email_to="your-email-here" subject="your subject here" label_submit="your label here"

= List form submissions in dashboard =
Via Settings > VSCF you can activate the listing of form submissions in your dashboard.

After activation you will notice a new menu item called "Submissions".

= SMTP =
SMTP (Simple Mail Transfer Protocol) is an internet standard for sending emails.

WordPress supports the PHP `mail()` function by default, but when using SMTP there's less chance your form submissions are being marked as spam.

You must install an additional plugin for this, such as:

* [WP mail SMTP](https://wordpress.org/plugins/wp-mail-smtp/)
* [Post SMTP](https://wordpress.org/plugins/post-smtp/)
* [Easy WP SMTP](https://wordpress.org/plugins/easy-wp-smtp/)

For support visit the relevant plugin forum.

= Uninstall =
If you uninstall plugin via dashboard all form submissions and settings will be removed from database.

All posts of the (custom) post type "submission" will be removed.

You can avoid this via Settings > VSCF.

= Question? =
Please take a look at the FAQ section.

= Translation =
Not included but plugin supports WordPress language packs.

More [translations](https://translate.wordpress.org/projects/wp-plugins/very-simple-contact-form) are very welcome!

= Credits =
Without the WordPress codex and help from the WordPress community I was not able to develop this plugin, so: thank you!

Enjoy!


== Installation ==
Please check Description section for installation info.


== Frequently Asked Questions ==
= About the FAQ =
The FAQ are updated regularly to include support for newly added or changed plugin features.

= How do I set plugin language? =
Plugin will use the website language, set in Settings > General.

If plugin isn't translated into this language, language fallback will be English.

= What is the default admin email address? =
By default form submissions will be send to the email address set in Settings > General.

You can change this via Settings > VSCF or by using an attribute.

= What is the default email subject? =
By default the email subject contains a prefix (the title of your website), followed by the subject that sender has filled in. If subject field is disabled it only contains the prefix (the title of your website).

You can change this by using an attribute.

The same subject will also be used in the confirmation email to sender.

= Why is the "from" email address not from sender? =
I have used a default so called "From" email header to avoid form submissions being marked as spam.

Best practice is using a "From" email header (an email address) that ends with your website domain.

That's why the default "From" email header starts with "wordpress" and ends with your website domain.

You can change this by using an attribute.

Your reply to sender will use another email header, called "Reply-To", which is the email address that sender has filled in.

= Can I display multiple forms on the same page? =
Do not add multiple shortcodes on the same page. This might cause a conflict.

But you can display a form by using the shortcode and a form by using the widget, on the same page.

= Why does form submission fail? =
An error message is displayed if plugin was unable to send form. This is often caused by the settings of your server.

Your hosting provider might have disabled the mail function of your server. Please contact them for info.

If they advice you to install a SMTP plugin, please check the "SMTP" section above.

In case you're using a SMTP plugin, check their settingspage for mistakes. Most SMTP plugins have a test module, you can test the mail function by sending a test mail.

= Why am I not receiving form submissions? =
* Please also check the junk/spam folder of your mailbox
* Check installation info above and check shortcode (attributes) for mistakes
* In case you're using a SMTP plugin, check their settingspage for mistakes
* Most SMTP plugins have a test module, you can test the mail function by sending a test mail
* Install another contact form plugin to determine whether it's caused by VSCF or not

= Does this plugin have anti-spam features? =
Of course, the native WordPress validating, sanitizing and escaping functions are included.

Form has a sum to solve and you can allow or disallow links in Message field.

And form contains honeypot fields and a time trap. This is not visible in the frontend of your website.

= Does this plugin meet the conditions of the GDPR? =
The General Data Protection Regulation (GDPR) is a regulation in EU law on data protection and privacy for all individuals within the European Union.

I did my best to meet the conditions of the GDPR:

* Form has a privacy consent checkbox
* You can disable collection of IP address
* Form submissions are safely stored in database, similar to how the native posts and pages are stored
* You can easily delete form submissions

= Does this plugin have its own block? =
No, plugin doesn't have its own block in the editor and there are no plans to add this anytime soon.

= No Semantic versioning? =
Version number doesn't give you info about the type of update (major, minor, patch). You should check changelog for that.

= How can I make a donation? =
You like my plugin and you're willing to make a donation? Thanks, I really appreciate that! There's a PayPal donate link at my website.

= Other question or comment? =
Please open a topic in plugin forum.


== Changelog ==
= Version 12.3 =
* Fix: max number of email addresses, when using email_to attribute
* Minor changes in code

= Version 12.2 =
* Fix: sending submission to multiple email addresses, when using email_to attribute
* Fix: Reply-To email address in confirmation email to sender

= Version 12.1 =
* Fix: "From" email header

= Version 12.0 =
* Fix: redirect when permalink is set to plain
* New: added sum to form
* You can disable sum via the settingspage (not recommended)
* New: setting to allow or disallow links in Message field
* Both features may help to reduce spam
* Changed privacy consent setting
* You may have to activate or deactivate privacy consent checkbox again

= Version 11.9 =
* Fix: custom CSS class

= Version 11.8 =
* Better validation of admin email address and "From" email header

= Version 11.7 =
* Fix: "From" email header
* This will fix submission not being send
* Re-added attribute for adding custom CSS class to form

= Version 11.6 =
* Removed captcha because spam bots are able to bybass it
* Added time trap (not visible in frontend)
* Better validating, sanitizing and escaping

= Version 11.5 =
* Fix: the subject from sender is added to submission when subject is overridden by the subject attribute
* Added CSS class to form (based on shortcode or widget)
* Removed attribute for adding custom CSS class to form
* Minor changes in code

= Version 11.4 =
* Fix: widget constructor
* This means you need to set existing widgets again
* Minor changes in code

For all versions please check file changelog.


== Screenshots ==
1. Very Simple Contact Form (GeneratePress theme).
2. Very Simple Contact Form (GeneratePress theme).
3. Very Simple Contact Form widget (GeneratePress theme).
4. Very Simple Contact Form widget (dashboard).
5. Very Simple Contact Form settingspage (dashboard).
6. Very Simple Contact Form settingspage (dashboard).
7. Very Simple Contact Form settingspage (dashboard).
8. Very Simple Contact Form submissions (dashboard).