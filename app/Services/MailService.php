<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class MailService
{
    /**
     * Builds the complete email template by combining header, body, and footer.
     *
     * @param int $eventId The event identifier.
     * @param string $lang The language code.
     * @param string $bodyName The specific body template name (e.g., "payment_reminder").
     * @return string The complete email template.
     */
    public static function buildEmailTemplate(int $eventId, string $lang, string $bodyName): string
    {
        $header = self::getTemplateSection($eventId, $lang, 'email_header');
        $body = self::getTemplateSection($eventId, $lang, $bodyName);
        $footer = self::getTemplateSection($eventId, $lang, 'email_footer');

        return "{$header}{$body}{$footer}";
    }

    /**
     * Retrieves a specific section of the email template from the database.
     *
     * @param int $eventId The event identifier.
     * @param string $lang The language code.
     * @param string $section The section name (header, body variant, footer).
     * @return string The section content.
     */
    private static function getTemplateSection(int $eventId, string $lang, string $section): string
    {
        return DB::table('email_templates')
            ->where([
                ['event_id', '=', $eventId],
                ['lang', '=', $lang],
                ['name', '=', $section] 
            ])
            ->value('body') ?? ''; // Return empty string if null
    }


    function replacePlaceholders(string $template, array $variables): string
{
    foreach ($variables as $key => $value) {
        $template = str_replace("{{" . $key . "}}", $value, $template);
    }
    return $template;
}
}
