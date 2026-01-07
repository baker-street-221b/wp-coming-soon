<?php
/**
 * Baker Street Coming Soon – Public Maintenance Page
 *
 * Diese Template-Datei ist bewusst so geschrieben, dass sie auch dann funktioniert,
 * wenn das Plugin deaktiviert ist (z.B. via /wartungsmodus/index.php).
 */

$option_key = 'bs_coming_soon_options';

$defaults = [
  'headline' => 'Website im Umbau',
  'subline' => "Unsere Website wird heute für einen kurzen Zeitraum umgestellt.\nWährenddessen sind wir selbstverständlich weiterhin per E-Mail oder Telefon für Sie erreichbar.",
  'phone_label' => '044 362 84 84',
  'phone_href'  => '0443628484',
  'email' => 'ief@ief-zh.ch',

  'logo_url' => '',
  'brand_text' => 'IEF',
  'brand_tagline' => "Institut für systemische Entwicklung\nund Fortbildung",
  'bg_color' => '#FFEA3D',
  'text_color' => '#2B2B2B',
  'card_bg' => '#FFFFFF',
  'font_family' => 'Whitney, sans-serif',
  'max_width' => 720,
];

$opts = [];
if (function_exists('get_option')) {
  $saved = get_option($option_key, []);
  if (is_array($saved)) $opts = $saved;
}
$opts = array_merge($defaults, is_array($opts) ? $opts : []);

// Basic escaping helpers (work with/without WP loaded)
$esc_html = function($s) {
  return function_exists('esc_html') ? esc_html($s) : htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
$esc_attr = function($s) {
  return function_exists('esc_attr') ? esc_attr($s) : htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};
$esc_url = function($s) {
  return function_exists('esc_url') ? esc_url($s) : htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
};

$headline = trim((string)$opts['headline']);
$subline_raw = (string)$opts['subline'];

// Sanitise subline (allow basic HTML when WP is available)
if (function_exists('wp_kses_post')) {
  $subline_html = wp_kses_post($subline_raw);
  // If the user didn't insert HTML tags, preserve line breaks.
  if (strip_tags($subline_html) === $subline_html) {
    $subline_html = nl2br($esc_html($subline_html));
  }
} else {
  $subline_html = nl2br($esc_html($subline_raw));
}

$phone_label = trim((string)$opts['phone_label']);
$phone_href  = trim((string)$opts['phone_href']);
if ($phone_href === '') {
  $phone_href = preg_replace('/[^0-9\+]/', '', $phone_label);
}

$email = trim((string)$opts['email']);

$logo_url = trim((string)$opts['logo_url']);
$brand_text = trim((string)$opts['brand_text']);
$brand_tagline = trim((string)$opts['brand_tagline']);

$bg_color = trim((string)$opts['bg_color']);
$text_color = trim((string)$opts['text_color']);
$card_bg = trim((string)$opts['card_bg']);
$font_family = trim((string)$opts['font_family']);
$max_width = (int)$opts['max_width'];
if ($max_width < 480) $max_width = 480;
if ($max_width > 1600) $max_width = 1600;

?><!doctype html>
<html lang="de">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="robots" content="noindex,nofollow,noarchive" />
  <title><?php echo $esc_html($headline ?: 'Wartungsmodus'); ?></title>

  <style>
    :root{
      --bs-bg: <?php echo $esc_attr($bg_color); ?>;
      --bs-text: <?php echo $esc_attr($text_color); ?>;
      --bs-card: <?php echo $esc_attr($card_bg); ?>;
      --bs-font: <?php echo $esc_attr($font_family); ?>;
      --bs-maxw: <?php echo (int)$max_width; ?>px;
    }

    html, body { height: 100%; }
    body {
      margin: 0;
      background: var(--bs-bg);
      color: var(--bs-text);
      font-family: var(--bs-font);
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    .bs-wrap {
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 40px 18px;
      box-sizing: border-box;
      gap: 28px;
    }

    .bs-brand {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
      text-align: center;
    }

    .bs-brand img {
      width: 280px;
      height: auto;
      display: block;
      margin-bottom: 32px;
    }

    .bs-brand-text {
      font-weight: 700;
      letter-spacing: 0.04em;
      font-size: 28px;
      line-height: 1;
    }

    .bs-brand-tagline {
      font-size: 14px;
      line-height: 1.35;
      opacity: 0.9;
      white-space: pre-line;
    }

    .bs-card {
      width: min(var(--bs-maxw), 100%);
      background: var(--bs-card);
      border-radius: 24px;
      padding: 56px 64px;
      box-sizing: border-box;
    }

    .bs-h1 {
      margin: 0 0 18px 0;
      font-weight: 700;
      letter-spacing: -0.02em;
      font-size: 32px;
      font-weight: 600;
      line-height: 1.05;
    }

    .bs-p {
      margin: 0;
      font-size: 20px;
      line-height: 1.55;
      max-width: 54ch;
    }

    .bs-contact {
      display: flex;
      flex-wrap: wrap;
      gap: 44px;
      margin-top: 32px;
      font-size: 20px;
      line-height: 1.4;
      font-weight: 600;
    }

    .bs-contact a {
      color: var(--bs-text);
      text-decoration: underline;
      text-decoration-thickness: 2px;
      text-underline-offset: 4px;
    }

    /* Responsive */
    @media (max-width: 900px) {
      .bs-card { padding: 40px 36px; }
      .bs-h1 { font-size: 32px; }
      .bs-p { font-size: 18px; }
      .bs-contact { font-size: 18px; gap: 28px; }
    }

    @media (max-width: 520px) {
      .bs-wrap { padding: 28px 14px; gap: 22px; }
      .bs-card { padding: 28px 22px; border-radius: 18px; }
      .bs-h1 { font-size: 34px; }
      .bs-p { font-size: 16px; }
      .bs-contact { font-size: 16px; margin-top: 22px; }
    }

    /* Print safety */
    @media print {
      body { background: #fff; }
      .bs-wrap { min-height: auto; }
      .bs-card { box-shadow: none; }
    }
  </style>
</head>
<body>
  <main class="bs-wrap">

    <div class="bs-brand" aria-label="Brand">
      <?php if (!empty($logo_url)) : ?>
        <img src="<?php echo $esc_url($logo_url); ?>" alt="<?php echo $esc_attr($brand_text ?: 'Logo'); ?>" />
      <?php else : ?>
        <div class="bs-brand-text"><?php echo $esc_html($brand_text ?: 'IEF'); ?></div>
      <?php endif; ?>

      <?php if (!empty($brand_tagline)) : ?>
        <div class="bs-brand-tagline"><?php echo $esc_html($brand_tagline); ?></div>
      <?php endif; ?>
    </div>

    <section class="bs-card" role="region" aria-label="Wartungsmodus">
      <h1 class="bs-h1"><?php echo $esc_html($headline); ?></h1>

      <div class="bs-p"><?php echo $subline_html; ?></div>

      <div class="bs-contact" aria-label="Kontakt">
        <?php if (!empty($phone_label)) : ?>
          <a href="tel:<?php echo $esc_attr($phone_href); ?>"><?php echo $esc_html($phone_label); ?></a>
        <?php endif; ?>

        <?php if (!empty($email)) : ?>
          <a href="mailto:<?php echo $esc_attr($email); ?>"><?php echo $esc_html($email); ?></a>
        <?php endif; ?>
      </div>
    </section>

  </main>
</body>
</html>
