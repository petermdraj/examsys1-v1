<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        foreach ([
            'platform_commission_default',
            'subscription_renewal_reminder_days',
            'subscription_grace_period_days',
            'razorpay_key',
            'razorpay_secret',
            'stripe_key',
            'stripe_secret',
            'stripe_webhook_secret',
            'paypal_client_id',
            'paypal_client_secret',
            'paypal_mode',
        ] as $key) {
            try {
                $this->migrator->delete("platform.{$key}");
            } catch (\Throwable) {
                // Key may not exist on fresh installs.
            }
        }

        try {
            $this->migrator->update('platform.hero_subtitle', fn () => 'Browse expert-made quizzes assigned by your lecturers. Attempt exams and earn verified certificates.');
        } catch (\Throwable) {
        }

        try {
            $this->migrator->update('platform.lecturer_cta_sub', fn () => 'Create quizzes and assign them to your students.');
        } catch (\Throwable) {
        }

        try {
            $this->migrator->update('platform.footer_tagline', fn () => 'AI-powered exam platform for institutions. Generate, assign, and certify. Self-hosted.');
        } catch (\Throwable) {
        }

        try {
            $this->migrator->update('platform.footer_columns', function (array $columns) {
                return array_map(function (array $column) {
                    $column['items'] = array_values(array_filter(
                        array_map(function (array $item) {
                            if (($item['url'] ?? '') === '/pricing') {
                                return ['label' => 'Lecturer Portal', 'url' => '/lecturer'];
                            }
                            if (($item['label'] ?? '') === 'Refund Policy') {
                                return null;
                            }

                            return $item;
                        }, $column['items'] ?? [])
                    ));

                    return $column;
                }, $columns);
            });
        } catch (\Throwable) {
        }
    }
};
