<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddHomepageAndFooterContentSettings extends SettingsMigration
{
    public function up(): void
    {
        // Homepage — Hero
        $this->migrator->add('platform.hero_title',          'Test your knowledge. Earn your certificate.');
        $this->migrator->add('platform.hero_subtitle',       'Browse thousands of expert-made quizzes. Attempt free or buy premium quizzes.');
        $this->migrator->add('platform.hero_cta_text',       'Browse Quizzes');
        $this->migrator->add('platform.hero_cta_url',        '');
        $this->migrator->add('platform.hero_secondary_text', 'See how it works');

        // Homepage — How It Works
        $this->migrator->add('platform.hiw_title',    'How it works');
        $this->migrator->add('platform.hiw_subtitle', 'Three simple steps to boost your exam preparation');
        $this->migrator->add('platform.hiw_steps', [
            ['num_label' => '01 — DISCOVER', 'title' => 'Find Your Quiz',        'desc' => 'Browse thousands of quizzes across all exam categories. Filter by subject, difficulty, or exam type.'],
            ['num_label' => '02 — ATTEMPT',  'title' => 'Take the Exam',         'desc' => 'IBPS-style interface — timed sections, question palette, negative marking.'],
            ['num_label' => '03 — CERTIFY',  'title' => 'Earn Your Certificate', 'desc' => 'Score well and get a verified PDF certificate with QR code. Share on LinkedIn.'],
        ]);

        // Homepage — Featured Section
        $this->migrator->add('platform.featured_title', 'Featured quizzes');

        // Homepage — Creator CTA Strip
        $this->migrator->add('platform.creator_cta_title', 'Are you a quiz creator?');
        $this->migrator->add('platform.creator_cta_sub',   'Sell your expertise.');
        $this->migrator->add('platform.creator_cta_btn',   'Learn more →');
        $this->migrator->add('platform.creator_cta_url',   '');

        // Footer
        $this->migrator->add('platform.footer_tagline',         'AI-powered quiz marketplace. Generate, publish, and sell quizzes in minutes. Self-hosted.');
        $this->migrator->add('platform.footer_show_newsletter', true);
        $this->migrator->add('platform.footer_columns', [
            ['title' => 'Product', 'items' => [
                ['label' => 'Home',             'url' => '/'],
                ['label' => 'Discover Quizzes', 'url' => '/quizzes'],
                ['label' => 'Pricing',          'url' => '/pricing'],
            ]],
            ['title' => 'Support', 'items' => [
                ['label' => 'Help Center', 'url' => '#'],
                ['label' => 'Contact',     'url' => '#'],
            ]],
            ['title' => 'Legal', 'items' => [
                ['label' => 'Privacy Policy', 'url' => '#'],
                ['label' => 'Terms of Use',   'url' => '#'],
                ['label' => 'Refund Policy',  'url' => '#'],
            ]],
            ['title' => 'Company', 'items' => [
                ['label' => 'About', 'url' => '#'],
                ['label' => 'Blog',  'url' => '#'],
            ]],
        ]);
    }
}
