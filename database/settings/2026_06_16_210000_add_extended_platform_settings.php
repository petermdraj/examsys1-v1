<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // General
        $this->migrator->add('platform.app_favicon', '');
        $this->migrator->add('platform.default_currency', 'USD');
        $this->migrator->add('platform.currency_symbol', '$');

        // Theme
        $this->migrator->add('platform.primary_color', '#6C2E63');
        $this->migrator->add('platform.accent_color', '#E0A431');

        // Registration & Auth
        $this->migrator->add('platform.allow_registration', true);
        $this->migrator->add('platform.require_email_verification', false);
        $this->migrator->add('platform.allow_social_login', false);
        $this->migrator->add('platform.google_client_id', '');
        $this->migrator->add('platform.google_client_secret', '');
        $this->migrator->add('platform.creator_registration_open', true);

        // AI / OpenAI
        $this->migrator->add('platform.openai_api_key', '');
        $this->migrator->add('platform.openai_organization', '');
        $this->migrator->add('platform.openai_model', 'gpt-4o');
        $this->migrator->add('platform.ai_max_questions_per_prompt', 50);

        // Payments — PayPal
        $this->migrator->add('platform.paypal_client_id', '');
        $this->migrator->add('platform.paypal_client_secret', '');
        $this->migrator->add('platform.paypal_mode', 'sandbox');

        // Storage / S3
        $this->migrator->add('platform.filesystem_disk', 'local');
        $this->migrator->add('platform.aws_key', '');
        $this->migrator->add('platform.aws_secret', '');
        $this->migrator->add('platform.aws_region', '');
        $this->migrator->add('platform.aws_bucket', '');
        $this->migrator->add('platform.aws_url', '');

        // Email — extras
        $this->migrator->add('platform.mail_encryption', 'tls');

        // SEO
        $this->migrator->add('platform.seo_title', 'Quizora — AI-Powered Quiz Platform');
        $this->migrator->add('platform.seo_description', 'Create, share and attempt AI-generated quizzes.');
        $this->migrator->add('platform.seo_keywords', '');
        $this->migrator->add('platform.google_analytics_id', '');
        $this->migrator->add('platform.og_image', '');

        // Social Links
        $this->migrator->add('platform.social_facebook', '');
        $this->migrator->add('platform.social_twitter', '');
        $this->migrator->add('platform.social_instagram', '');
        $this->migrator->add('platform.social_youtube', '');
        $this->migrator->add('platform.social_linkedin', '');

        // Maintenance
        $this->migrator->add('platform.maintenance_mode', false);
        $this->migrator->add('platform.maintenance_message', 'We are performing scheduled maintenance. Back soon!');
    }

    public function down(): void
    {
        foreach ([
            'app_favicon','default_currency','currency_symbol',
            'primary_color','accent_color',
            'allow_registration','require_email_verification','allow_social_login',
            'google_client_id','google_client_secret','creator_registration_open',
            'openai_api_key','openai_organization','openai_model','ai_max_questions_per_prompt',
            'paypal_client_id','paypal_client_secret','paypal_mode',
            'filesystem_disk','aws_key','aws_secret','aws_region','aws_bucket','aws_url',
            'mail_encryption',
            'seo_title','seo_description','seo_keywords','google_analytics_id','og_image',
            'social_facebook','social_twitter','social_instagram','social_youtube','social_linkedin',
            'maintenance_mode','maintenance_message',
        ] as $key) {
            $this->migrator->delete("platform.{$key}");
        }
    }
};
