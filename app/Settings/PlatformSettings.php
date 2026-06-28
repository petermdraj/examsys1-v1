<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class PlatformSettings extends Settings
{
    // General
    public string  $app_name         = 'Quizora';
    public ?string $app_logo         = null;
    public ?string $app_favicon      = null;
    public ?string $certificate_logo = null;
    public string  $default_currency = 'USD';
    public string  $currency_symbol  = '$';
    // Theme — colors
    public string $primary_color = '#6C2E63';
    public string $accent_color  = '#E0A431';

    // Theme — typography
    public string $font_primary   = 'Inter';          // body / UI text
    public string $font_display   = 'Plus Jakarta Sans'; // headings / display
    public string $font_size_base = '16px';           // root font-size (14px–20px)

    // Registration & Auth
    public bool   $allow_registration         = true;
    public bool   $require_email_verification = false;
    public bool   $allow_social_login         = false;
    public string $google_client_id           = '';
    public string $google_client_secret       = '';
    public bool   $creator_registration_open  = true;

    // AI / OpenAI
    public string $openai_api_key              = '';
    public string $openai_organization         = '';
    public string $openai_model                = 'gpt-4o';
    public int    $ai_free_generations_default  = 10;
    public float  $ai_charge_per_gen_default    = 5.00;
    public int    $ai_max_questions_per_prompt  = 50;

    // Commission
    public float $platform_commission_default = 20.0;

    // Subscription lifecycle
    public int $subscription_renewal_reminder_days = 7;
    public int $subscription_grace_period_days     = 3;

    // Payments
    public string $razorpay_key          = '';
    public string $razorpay_secret       = '';
    public string $stripe_key            = '';
    public string $stripe_secret         = '';
    public string $stripe_webhook_secret = '';
    public string $paypal_client_id      = '';
    public string $paypal_client_secret  = '';
    public string $paypal_mode           = 'sandbox';

    // Storage
    public string $filesystem_disk = 'local';
    public string $aws_key         = '';
    public string $aws_secret      = '';
    public string $aws_region      = '';
    public string $aws_bucket      = '';
    public string $aws_url         = '';

    // Email / SMTP
    public string $mail_host         = '';
    public string $mail_port         = '587';
    public string $mail_encryption   = 'tls';
    public string $mail_username     = '';
    public string $mail_password     = '';
    public string $mail_from_address = '';
    public string $mail_from_name    = '';

    // SEO
    public string  $seo_title           = '';
    public string  $seo_description     = 'Create, share and attempt AI-generated quizzes.';
    public string  $seo_keywords        = '';
    public string  $google_analytics_id = '';
    public ?string $og_image            = null;

    // Social Links
    public string $social_facebook  = '';
    public string $social_twitter   = '';
    public string $social_instagram = '';
    public string $social_youtube   = '';
    public string $social_linkedin  = '';

    // Homepage — Hero
    public string $hero_title          = 'Test your knowledge. Earn your certificate.';
    public string $hero_subtitle       = 'Browse thousands of expert-made quizzes. Attempt free or buy premium quizzes.';
    public string $hero_cta_text       = 'Browse Quizzes';
    public string $hero_cta_url        = '';
    public string $hero_secondary_text = 'See how it works';

    // Homepage — How It Works
    public string $hiw_title    = 'How it works';
    public string $hiw_subtitle = 'Three simple steps to boost your exam preparation';
    /** Array of how-it-works steps: [["num_label"=>"...","title"=>"...","desc"=>"..."]] */
    public array $hiw_steps = [
        ['num_label' => '01 — DISCOVER', 'title' => 'Find Your Quiz',         'desc' => 'Browse thousands of quizzes across all exam categories. Filter by subject, difficulty, or exam type.'],
        ['num_label' => '02 — ATTEMPT',  'title' => 'Take the Exam',          'desc' => 'IBPS-style interface — timed sections, question palette, negative marking.'],
        ['num_label' => '03 — CERTIFY',  'title' => 'Earn Your Certificate',  'desc' => 'Score well and get a verified PDF certificate with QR code. Share on LinkedIn.'],
    ];

    // Homepage — Featured Quizzes
    public string $featured_title = 'Featured quizzes';

    // Homepage — Creator CTA Strip
    public string $creator_cta_title = 'Are you a quiz creator?';
    public string $creator_cta_sub   = 'Sell your expertise.';
    public string $creator_cta_btn   = 'Learn more →';
    public string $creator_cta_url   = '';

    // Homepage — Stats Strip
    public string $homepage_stat_1_label = '';
    public string $homepage_stat_1_value = '';
    public string $homepage_stat_2_label = '';
    public string $homepage_stat_2_value = '';
    public string $homepage_stat_3_label = '';
    public string $homepage_stat_3_value = '';

    // Footer
    public string $footer_tagline         = 'AI-powered quiz marketplace. Generate, publish, and sell quizzes in minutes. Self-hosted.';
    public bool   $footer_show_newsletter = true;
    /** Array of footer column groups: [["title"=>"...","items"=>[["label"=>"...","url"=>"..."]]]] */
    public array $footer_columns = [
        ['title' => 'Product', 'items' => [['label' => 'Home', 'url' => '/'], ['label' => 'Discover Quizzes', 'url' => '/quizzes'], ['label' => 'Pricing', 'url' => '/pricing']]],
        ['title' => 'Support', 'items' => [['label' => 'Help Center', 'url' => '#'], ['label' => 'Contact', 'url' => '#']]],
        ['title' => 'Legal',   'items' => [['label' => 'Privacy Policy', 'url' => '#'], ['label' => 'Terms of Use', 'url' => '#'], ['label' => 'Refund Policy', 'url' => '#']]],
        ['title' => 'Company', 'items' => [['label' => 'About', 'url' => '#'], ['label' => 'Blog', 'url' => '#']]],
    ];

    // Language switcher
    /** Locale codes that are active and visible in the switcher (e.g. ['en','hi','de']) */
    public array $enabled_locales       = ['en','hi','de','nl','da','no','sv','fr','ja'];
    public bool  $show_switcher_admin   = true;
    public bool  $show_switcher_creator = true;
    public bool  $show_switcher_front   = true;

    // Custom & hidden locales (managed via Translation Dashboard)
    /** Admin-added custom locales: assoc array of code => native name, e.g. ['es' => 'Español'] */
    public array $extra_locales  = [];
    /** Admin-added locale flags: assoc array of code => emoji, e.g. ['es' => '🇪🇸'] */
    public array $extra_locale_flags = [];
    /** RTL locale codes, e.g. ['ar', 'he'] */
    public array $rtl_locales    = [];
    /** Built-in locale codes hidden from the dashboard (admin deleted them) */
    public array $hidden_locales = [];

    // Maintenance
    public bool   $maintenance_mode    = false;
    public string $maintenance_message = 'We are performing scheduled maintenance. Back soon!';

    public static function group(): string
    {
        return 'platform';
    }
}
