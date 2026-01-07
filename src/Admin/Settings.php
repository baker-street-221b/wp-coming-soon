<?php
namespace BakerStreet\ComingSoon\Admin;

defined('ABSPATH') || exit;

use BakerStreet\ComingSoon\Plugin;

final class Settings {
    public static function init(): void {
        add_action('admin_menu', [__CLASS__, 'add_settings_page']);
        add_action('admin_init', [__CLASS__, 'register_settings']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);

        // When options update, ensure /wartungsmodus is synced
        add_action('update_option_' . Plugin::OPTION_KEY, function () {
            Plugin::ensure_public_endpoint_files();
        }, 10, 0);
    }

    public static function add_settings_page(): void {
        add_options_page(
            __('Coming Soon', 'bakerstreet-coming-soon'),
            __('Coming Soon', 'bakerstreet-coming-soon'),
            'manage_options',
            'bakerstreet-coming-soon',
            [__CLASS__, 'render_page']
        );
    }

    public static function register_settings(): void {
        register_setting(
            'bscs_settings_group',
            Plugin::OPTION_KEY,
            [
                'type' => 'array',
                'sanitize_callback' => [__CLASS__, 'sanitize'],
                'default' => Plugin::defaults(),
            ]
        );

        add_settings_section(
            'bscs_section_main',
            __('Anzeige', 'bakerstreet-coming-soon'),
            function () {
                echo '<p style="max-width:820px;">';
                echo esc_html__('Wenn aktiviert, sehen nicht eingeloggte Besucher die Wartungsseite. Eingeloggte Benutzer sehen die Website normal.', 'bakerstreet-coming-soon');
                echo '<br>';
                echo esc_html__('Zusätzlich wird /wartungsmodus im Webroot angelegt (noindex) und bleibt auch bei deaktiviertem Plugin erreichbar.', 'bakerstreet-coming-soon');
                echo '</p>';
            },
            'bakerstreet-coming-soon'
        );

        $fields = [
            'enabled' => __('Wartungsmodus aktiv', 'bakerstreet-coming-soon'),
            'headline' => __('Headline', 'bakerstreet-coming-soon'),
            'subline' => __('Subline/Text', 'bakerstreet-coming-soon'),
            'phone_label' => __('Telefon (Anzeige)', 'bakerstreet-coming-soon'),
            'phone_href' => __('Telefon (Link, optional)', 'bakerstreet-coming-soon'),
            'email' => __('E-Mail', 'bakerstreet-coming-soon'),
            'logo_url' => __('Logo (URL)', 'bakerstreet-coming-soon'),
            'brand_text' => __('Brand-Text (Fallback)', 'bakerstreet-coming-soon'),
            'brand_tagline' => __('Brand-Tagline (optional)', 'bakerstreet-coming-soon'),
            'bg_color' => __('Hintergrundfarbe', 'bakerstreet-coming-soon'),
            'text_color' => __('Textfarbe', 'bakerstreet-coming-soon'),
            'card_bg' => __('Kartenfarbe', 'bakerstreet-coming-soon'),
            'font_family' => __('Font-Family', 'bakerstreet-coming-soon'),
            'max_width' => __('Max. Kartenbreite (px)', 'bakerstreet-coming-soon'),
        ];

        foreach ($fields as $key => $label) {
            add_settings_field(
                'bscs_' . $key,
                $label,
                [__CLASS__, 'render_field'],
                'bakerstreet-coming-soon',
                'bscs_section_main',
                ['key' => $key]
            );
        }
    }

    public static function sanitize($input): array {
        $out = Plugin::get_options();
        $in = is_array($input) ? $input : [];

        $out['enabled'] = (!empty($in['enabled']) && $in['enabled'] === '1') ? '1' : '0';
        $out['headline'] = isset($in['headline']) ? sanitize_text_field($in['headline']) : $out['headline'];

        if (isset($in['subline'])) {
            $sub = wp_kses_post($in['subline']);
            $sub = preg_replace("/\r\n|\r/", "\n", $sub);
            $out['subline'] = $sub;
        }

        $out['phone_label'] = isset($in['phone_label']) ? sanitize_text_field($in['phone_label']) : $out['phone_label'];
        $out['phone_href']  = isset($in['phone_href']) ? preg_replace('/[^0-9\+]/', '', (string)$in['phone_href']) : $out['phone_href'];

        if (isset($in['email'])) {
            $email = sanitize_email($in['email']);
            $out['email'] = $email ?: $out['email'];
        }

        // Logo URL from media library
        $out['logo_url'] = isset($in['logo_url']) ? esc_url_raw($in['logo_url']) : $out['logo_url'];

        $out['brand_text'] = isset($in['brand_text']) ? sanitize_text_field($in['brand_text']) : $out['brand_text'];
        if (isset($in['brand_tagline'])) {
            $tag = sanitize_textarea_field($in['brand_tagline']);
            $tag = preg_replace("/\r\n|\r/", "\n", $tag);
            $out['brand_tagline'] = $tag;
        }

        // Colors: allow hex or rgb/rgba... keep simple, strip tags
        foreach (['bg_color','text_color','card_bg'] as $cKey) {
            if (isset($in[$cKey])) {
                $val = trim((string)$in[$cKey]);
                $val = wp_strip_all_tags($val);
                $out[$cKey] = $val !== '' ? $val : $out[$cKey];
            }
        }

        $out['font_family'] = isset($in['font_family']) ? wp_strip_all_tags((string)$in['font_family']) : $out['font_family'];

        if (isset($in['max_width'])) {
            $mw = (int)$in['max_width'];
            if ($mw < 480) $mw = 480;
            if ($mw > 1600) $mw = 1600;
            $out['max_width'] = $mw;
        }

        return $out;
    }

    public static function enqueue_assets(string $hook): void {
        if ($hook !== 'settings_page_bakerstreet-coming-soon') {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');

        wp_enqueue_script(
            'bscs-admin',
            BSCS_PLUGIN_URL . 'assets/admin.js',
            ['jquery', 'wp-color-picker'],
            BSCS_VERSION,
            true
        );
    }

    public static function render_field(array $args): void {
        $key = $args['key'] ?? '';
        $opts = Plugin::get_options();
        $name = Plugin::OPTION_KEY;

        switch ($key) {
            case 'enabled':
                printf(
                    '<label><input type="checkbox" name="%1$s[enabled]" value="1" %2$s> %3$s</label><p class="description">%4$s <a href="%5$s" target="_blank" rel="noopener">/wartungsmodus</a></p>',
                    esc_attr($name),
                    checked('1', $opts['enabled'], false),
                    esc_html__('Nicht eingeloggte Besucher sehen die Wartungsseite', 'bakerstreet-coming-soon'),
                    esc_html__('Testseite (immer noindex):', 'bakerstreet-coming-soon'),
                    esc_url(home_url('/wartungsmodus/'))
                );
                break;

            case 'headline':
                printf(
                    '<input type="text" class="regular-text" name="%1$s[headline]" value="%2$s" />',
                    esc_attr($name),
                    esc_attr($opts['headline'])
                );
                break;

            case 'subline':
                printf(
                    '<textarea class="large-text" rows="6" name="%1$s[subline]">%2$s</textarea><p class="description">%3$s</p>',
                    esc_attr($name),
                    esc_textarea($opts['subline']),
                    esc_html__('Zeilenumbrüche sind erlaubt. HTML ist möglich (z.B. <br>).', 'bakerstreet-coming-soon')
                );
                break;

            case 'phone_label':
                printf(
                    '<input type="text" class="regular-text" name="%1$s[phone_label]" value="%2$s" />',
                    esc_attr($name),
                    esc_attr($opts['phone_label'])
                );
                echo '<p class="description">' . esc_html__('Wird sichtbar angezeigt.', 'bakerstreet-coming-soon') . '</p>';
                break;

            case 'phone_href':
                printf(
                    '<input type="text" class="regular-text" name="%1$s[phone_href]" value="%2$s" placeholder="0443628484" />',
                    esc_attr($name),
                    esc_attr($opts['phone_href'])
                );
                echo '<p class="description">' . esc_html__('Optional. Wenn leer, wird aus der Anzeige eine Nummer abgeleitet.', 'bakerstreet-coming-soon') . '</p>';
                break;

            case 'email':
                printf(
                    '<input type="email" class="regular-text" name="%1$s[email]" value="%2$s" />',
                    esc_attr($name),
                    esc_attr($opts['email'])
                );
                break;

            case 'logo_url':
                $logo = (string)$opts['logo_url'];
                printf(
                    '<input type="url" class="regular-text" id="bscs_logo_url" name="%1$s[logo_url]" value="%2$s" /> ',
                    esc_attr($name),
                    esc_attr($logo)
                );
                echo '<button type="button" class="button" id="bscs_logo_select">' . esc_html__('Logo auswählen', 'bakerstreet-coming-soon') . '</button> ';
                echo '<button type="button" class="button" id="bscs_logo_clear">' . esc_html__('Entfernen', 'bakerstreet-coming-soon') . '</button>';
                echo '<div style="margin-top:10px;">';
                if ($logo) {
                    echo '<img id="bscs_logo_preview" src="' . esc_url($logo) . '" alt="Logo" style="max-width:280px;height:auto;display:block;">';
                } else {
                    echo '<img id="bscs_logo_preview" src="" alt="" style="max-width:280px;height:auto;display:none;">';
                }
                echo '</div>';
                break;

            case 'brand_text':
                printf(
                    '<input type="text" class="regular-text" name="%1$s[brand_text]" value="%2$s" />',
                    esc_attr($name),
                    esc_attr($opts['brand_text'])
                );
                echo '<p class="description">' . esc_html__('Wird angezeigt, wenn kein Logo gesetzt ist.', 'bakerstreet-coming-soon') . '</p>';
                break;

            case 'brand_tagline':
                printf(
                    '<textarea class="large-text" rows="3" name="%1$s[brand_tagline]">%2$s</textarea>',
                    esc_attr($name),
                    esc_textarea($opts['brand_tagline'])
                );
                break;

            case 'bg_color':
            case 'text_color':
            case 'card_bg':
                printf(
                    '<input type="text" class="bscs-color" name="%1$s[%2$s]" value="%3$s" data-default-color="%3$s" />',
                    esc_attr($name),
                    esc_attr($key),
                    esc_attr($opts[$key])
                );
                break;

            case 'font_family':
                printf(
                    '<input type="text" class="regular-text" name="%1$s[font_family]" value="%2$s" placeholder="Whitney, sans-serif" />',
                    esc_attr($name),
                    esc_attr($opts['font_family'])
                );
                break;

            case 'max_width':
                printf(
                    '<input type="number" min="480" max="1600" step="1" name="%1$s[max_width]" value="%2$d" />',
                    esc_attr($name),
                    (int)$opts['max_width']
                );
                break;
        }
    }

    public static function render_page(): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Coming Soon', 'bakerstreet-coming-soon') . '</h1>';
        echo '<form method="post" action="options.php">';
        settings_fields('bscs_settings_group');
        do_settings_sections('bakerstreet-coming-soon');
        submit_button();
        echo '</form>';
        echo '</div>';
    }
}
