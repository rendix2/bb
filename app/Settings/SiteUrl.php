<?php declare(strict_types=1);

namespace App\Settings;

/**
 * The absolute URL of the web (parameter env.url, e.g. http://localhost:9380): links in mails are generated against it,
 * so they are right also from the console (cron) and the tests, where there is no HTTP request.
 *
 * @package App\Settings
 */
class SiteUrl extends Setting
{
}
