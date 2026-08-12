# Telegram Bot WordPress Plugin Boilerplate

A **scalable and advanced** WordPress boilerplate plugin for connecting a Telegram bot to your website.

`v3.3.0` ([Changelog](CHANGELOG.md))

Libraries used: [PHP Telegram Bot](https://GitHub.com/php-telegram-bot/core)

## Requirements

- **PHP**: 8.0 or higher
- **WordPress**: 5.0 or higher
- **Composer**: For dependency management

## Installation

Clone or download this repository, and then you'll need to do a four-step **CASE-SENSITIVE** find and replace in all the codes:
1. Search for `TelegramPluginBoilerplate` to capture the namespaces.
2. Search for `FDTBWPB` to capture the constants.
3. Search for `fdtbwpb` to capture option name and slugs.
4. Search for `telegram-plugin-boilerplate` to capture the text domains.

Then, update the header in `plugin.php` with your own information.

## Composer Setup

**AFTER** you have done the find and replace, run this command in the plugin root:
```bash
$ composer install
```

## Bot Setup

### Create Bot
Create a bot with [@BotFather](https://t.me/BotFather) and copy the token.

## Plugin setup
1. Enable the plugin
2. Copy bot's token and username (with `@`) in the plugin's settings
3. Open the `options-permalink.php` page so that the API endpoints get refreshed

### Use `getUpdates` method (Local)
To test the bot on a local environment, add this constant to your `wp-config.php` file: 
```
define('WP_ENVIRONMENT_TYPE', 'local');
```
And use this endpoint to handle the updates (e.g. each time you message the bot):
```
https://{WEBSITE.COM}/wp-json/fdtbwpb/v1/get-message-polling
```

### Set Webhook (Production)
To enable your bot for a production website, set the bot's webhook to the `get-message` endpoint:
```
https://api.telegram.org/bot{TOKEN}/setWebhook?url=https://{WEBSITE.COM}/wp-json/fdtbwpb/v1/get-message
```

## Redirect requests (proxy)

If your server can't access Telegram, you can use a middleman server to redirect requests:
- Change `CURLOPT_URL`'s value in [forward.php](forward.php)
- Upload [forward.php](forward.php) and [forward-to-telegram.php](forward-to-telegram.php) to your middleman server
- Use `forward`'s URL as bot's webhook
- Copy the `forward-to-telegram`'s full link in the plugin's settings

## Debugging

You can see bot's error log file here:
```
wp-content/plugins/telegram-plugin-boilerplate/logs/
```

## More Examples and Documentation

You can find more examples and sample codes/methods/classes in the base [WordPress Plugin Boilerplate](https://github.com/FourteenDev/wordpress-plugin-boilerplate) repository.

## Translating the Plugin

1. Open terminal in the plugin root, and run this command to generate the POT file with WP-CLI:
```bash
$ wp i18n make-pot . languages/telegram-plugin-boilerplate.pot
```

2. Create translations with PoEdit:
	- Open PoEdit and choose "Create new translation" or "New from POT file".
	- Select `languages/telegram-plugin-boilerplate.pot`.
	- Choose your target language locale, for example `de_DE` for German.
	- Translate each string.
	- Save the file as `languages/telegram-plugin-boilerplate-de_DE.po`.
	- PoEdit will automatically generate `languages/telegram-plugin-boilerplate-de_DE.mo`.

3. Keep translations up to date:
   - After adding or changing strings, regenerate the POT file.
   - Reopen the updated POT in PoEdit and choose "Update from POT file" to update your translations.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
