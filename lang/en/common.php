<?php

return [

    // App
    'app_name'                => config('app.name', 'ExamSys'),

    // Pubbar (secondary discovery bar)
    'pubbar_explore'          => 'Explore',
    'pubbar_quizzes'          => 'Quizzes',
    'pubbar_categories'       => 'Categories',
    'pubbar_for_lecturers'     => 'For creators',
    'pubbar_search'           => 'Search quizzes…',

    // Navigation
    'nav_discover'           => 'Discover',
    'nav_for_lecturers'       => 'For Creators',
    'nav_pricing'            => 'Pricing',
    'nav_login'              => 'Log in',
    'nav_signup'             => 'Sign up free',
    'nav_open_menu'          => 'Open menu',

    // User dropdown
    'user_admin_panel'       => 'Admin panel',
    'user_lecturer_dashboard' => 'Creator dashboard',
    'user_my_dashboard'      => 'My dashboard',
    'user_my_attempts'       => 'My attempts',
    'user_certificates'      => 'Certificates',
    'user_account_settings'  => 'Account Settings',
    'user_logout'            => 'Log out',

    // Footer
    'footer_all_rights'      => 'All rights reserved.',

    // Flash / system messages
    'flash_link_copied'      => 'Link copied!',

    // Buttons / generic actions
    'save'                   => 'Save',
    'cancel'                 => 'Cancel',
    'delete'                 => 'Delete',
    'edit'                   => 'Edit',
    'submit'                 => 'Submit',
    'search'                 => 'Search',
    'filter'                 => 'Filter',
    'reset'                  => 'Reset',
    'view'                   => 'View',
    'view_all'               => 'View all',
    'load_more'              => 'Load more',
    'go_back'                => 'Go back',
    'continue'               => 'Continue',
    'confirm'                => 'Confirm',
    'close'                  => 'Close',
    'next'                   => 'Next',
    'previous'               => 'Previous',

    // Status labels
    'status_active'          => 'Active',
    'status_inactive'        => 'Inactive',
    'status_draft'           => 'Draft',
    'status_published'       => 'Published',
    'status_archived'        => 'Archived',
    'status_pending'         => 'Pending',
    'status_paid'            => 'Paid',
    'status_failed'          => 'Failed',
    'status_free'            => 'Free',

    // Pagination
    'pagination_previous'    => 'Previous',
    'pagination_next'        => 'Next',
    'pagination_showing'     => 'Showing :from to :to of :total results',

    // Alerts / notifications
    'success'                => 'Success',
    'error'                  => 'Error',
    'warning'                => 'Warning',
    'info'                   => 'Info',

    // Pricing page
    'pricing_title'          => 'Plans for every creator',
    'pricing_eyebrow'        => 'Simple, transparent pricing',
    'pricing_subtitle'       => "Start free. Upgrade when you're ready to sell paid quizzes and earn more.",
    'pricing_billing_monthly' => 'Monthly',
    'pricing_billing_yearly' => 'Yearly',
    'pricing_save_badge'     => 'Save up to 25%',
    'pricing_most_popular'   => 'Most popular',
    'pricing_your_plan'      => 'Your plan',
    'pricing_free'           => 'Free',
    'pricing_per_month'      => '/ month',
    'pricing_per_year'       => '/ year',
    'pricing_unlimited_quizzes'    => 'Unlimited published quizzes',
    'pricing_up_to_quizzes'        => 'Up to :count published quizzes',
    'pricing_free_ai_generations'  => ':count free AI generations',
    'pricing_commission_rate'      => ':rate% platform commission',
    'pricing_current_plan_btn'     => 'Current plan',
    'pricing_switch_free'          => 'Switch to Free',
    'pricing_get_started_free'     => 'Get started free',
    'pricing_upgrade_to'           => 'Upgrade to :name',
    'become_lecturer'       => 'Become a creator',
    'pricing_get_plan'             => 'Get :name',
    'pricing_secure_payments'      => 'Secure payments',
    'pricing_secure_payments_desc' => 'Powered by Razorpay & Stripe. Your card details never touch our servers.',
    'pricing_cancel_anytime'       => 'Cancel anytime',
    'pricing_cancel_anytime_desc'  => 'No lock-ins. Downgrade or cancel your plan at any time.',
    'pricing_need_help'            => 'Need help choosing?',
    'pricing_need_help_desc'       => 'Start with Free — you can always upgrade as your quiz library grows.',

    // Dashboard
    'welcome_back'             => 'Welcome back',
    'quizzes_enrolled'         => 'Quizzes Enrolled',
    'pass_rate'                => 'Pass Rate',
    'certificates_earned'      => 'Certificates Earned',
    'recent_attempts'          => 'Recent Attempts',
    'result'                   => 'Result',
    'havent_attempted_yet'     => "You haven't attempted any quizzes yet.",

    // Generic page misc
    'view_all_quizzes'       => 'View all quizzes →',
    'no_quizzes_yet'         => 'No quizzes published yet.',
    'create_first_quiz_link' => 'Create the first one →',
    'questions_count'        => ':count question|:count questions',
    'duration_min'           => ':count min',
    'attempts_count'         => ':count attempt|:count attempts',
    'explore_by_category'    => 'Explore by category',
    'free_badge'             => 'Free',
    'start_free'             => 'Start free',
    'get_started'            => 'Get started',

    // Language switcher
    'language'               => 'Language',
    'lang_en'                => 'English',
    'lang_hi'                => 'हिन्दी',

    // Profile controller messages
    'profile_updated'                   => 'Profile updated successfully.',
    'avatar_updated'                    => 'Avatar updated.',
    'password_incorrect'                => 'Current password is incorrect.',
    'password_updated'                  => 'Password updated successfully.',
    'notifications_saved'               => 'Notification preferences saved.',
    'account_deleted'                   => 'Your account has been deleted.',

    // Subscription controller messages
    'lecturers_only'                     => 'Only creators can subscribe to plans.',
    'payment_gateway_not_configured'    => 'Payment gateway is not configured. Please contact the administrator.',
    'payment_verification_failed'       => 'Payment verification failed. Please try again.',
    'subscription_activated'            => 'You are now on the :plan plan!',
    'subscription_switched'             => 'Switched to the :plan plan.',
    'stripe_not_configured'             => 'Stripe is not configured.',
    'razorpay_not_configured'           => 'Razorpay is not configured.',
    'payment_session_invalid'           => 'Invalid payment session.',
    'payment_verify_failed'             => 'Could not verify payment.',
    'payment_not_completed'             => 'Payment was not completed. Please try again.',

    // Categories page
    'categories_title'              => 'Categories',
    'categories_heading'            => 'Browse by Category',
    'categories_subtitle'           => ':count :word · click any to explore quizzes',
    'categories_none'               => 'No categories yet.',

    // Certificate verify page
    'certificate_of_completion'     => 'Certificate of completion',
    'certificate_has_completed'     => 'has successfully completed',
    'certificate_issued_on'         => 'Issued on :date',
    'certificate_verified_badge'    => '✓ This certificate has been verified',

    // Certificates list page
    'certificates_achievements'     => 'Achievements',
    'certificates_my_heading'       => 'My Certificates',
    'certificates_issued'           => 'Issued :date',
    'certificates_passed_pct'       => ':pct% — Passed',
    'certificates_view_btn'         => 'View',
    'certificates_download_btn'     => 'Download',
    'certificates_none_heading'     => 'No certificates yet',
    'certificates_none_body'        => 'Pass a quiz that has certificates enabled to earn your first one.',
    'certificates_go_quizzes'       => 'Go to My Quizzes',

    // Checkout page
    'checkout_heading'              => 'Complete Purchase',
    'checkout_buying_access'        => "You're buying access to :quiz",
    'checkout_quiz_access'          => 'Quiz Access',
    'checkout_total'                => 'Total',
    'checkout_no_gateway'           => 'No payment gateway is configured. Please contact the site administrator.',
    'checkout_pay_razorpay'         => 'Pay with Razorpay',
    'checkout_pay_stripe'           => 'Pay with Stripe',
    'checkout_pay_paypal'           => 'Pay with PayPal',
    'checkout_secure_note'          => 'Secure payment · Instant access after payment',

    // Dashboard
    'my_dashboard_title'            => 'My Dashboard',
    'view_all_arrow'                => 'View all →',

    // Profile page
    'account_settings_title'        => 'Account Settings',
    'account_settings_sub'          => 'Manage your profile, password, and preferences.',
    'profile_info_heading'          => 'Profile Information',
    'profile_full_name'             => 'Full Name *',
    'profile_phone'                 => 'Phone Number',
    'profile_country'               => 'Country',
    'profile_timezone'              => 'Timezone',
    'profile_email_address'         => 'Email Address',
    'profile_email_note'            => 'Email cannot be changed here. Contact support.',
    'profile_save_btn'              => 'Save Profile',
    'change_password_heading'       => 'Change Password',
    'current_password_label'        => 'Current Password *',
    'new_password_label'            => 'New Password *',
    'confirm_new_password_label'    => 'Confirm New Password *',
    'update_password_btn'           => 'Update Password',
    'notifications_heading'         => 'Notification Preferences',
    'notifications_sub'             => "Choose which emails you'd like to receive.",
    'notify_quiz_results'           => 'Quiz Results',
    'notify_quiz_results_sub'       => 'Get an email when your quiz attempt is scored',
    'notify_purchases'              => 'Purchase Confirmations',
    'notify_purchases_sub'          => 'Receipt emails after buying a quiz',
    'notify_weekly_digest'          => 'Weekly Digest',
    'notify_weekly_digest_sub'      => 'A weekly summary of new quizzes in your categories',
    'save_preferences_btn'          => 'Save Preferences',
    'purchase_history_heading'      => 'Purchase History',
    'paid_badge'                    => 'Paid',
    'danger_zone_heading'           => 'Danger Zone',
    'danger_zone_body'              => 'Deleting your account removes your data within 30 days. This action is permanent after that window.',
    'delete_account_btn'            => 'Delete My Account',
    'delete_modal_heading'          => 'Delete your account?',
    'delete_modal_body'             => 'Enter your password to confirm. Your data will be retained for 30 days before permanent deletion.',
    'delete_modal_your_password'    => 'Your Password *',
    'delete_confirm_btn'            => 'Yes, delete my account',
    'cancel_btn'                    => 'Cancel',
    'select_country_placeholder'    => '— Select country —',
    'select_timezone_placeholder'   => '— Select timezone —',
    'search_country_placeholder'    => 'Search country…',
    'search_timezone_placeholder'   => 'Search timezone…',
    'no_results_select'             => 'No results',

    // Newsletter
    'newsletter_subscribed'             => "You're subscribed! Thanks for joining.",
    'newsletter_already_subscribed'     => "You're already subscribed.",
    'newsletter_subscribe_btn'          => 'Subscribe',
    'newsletter_email_placeholder'      => 'Your email…',

    // Home themes — students
    'browse_by_subject'                 => 'Browse by subject',
    'students_hero_eyebrow'             => 'Free quizzes · certificates · every subject',
    'students_hero_h1'                  => 'Test what you know. Get certified.',
    'students_hero_sub'                 => 'Thousands of quizzes across every subject — science, maths, history, coding, and more. Free quizzes to start, premium deep-dives to master.',
    'students_browse_free'              => 'Browse Free Quizzes',
    'students_see_all_categories'       => 'See All Categories',
    'students_cert_title'               => 'Certificate of Achievement',
    'hiw_step1_discover_title'          => 'Discover Quizzes',
    'hiw_step1_discover_desc'           => 'Browse free and paid quizzes across any subject, sorted by popularity and difficulty.',
    'hiw_step2_attempt_title'           => 'Attempt at Your Pace',
    'hiw_step2_attempt_desc'            => 'Timed or untimed. Multiple attempts. Instant results with answer explanations.',
    'hiw_step3_cert_title'              => 'Download Certificate',
    'hiw_step3_cert_desc'               => 'Pass the quiz and get a verifiable PDF certificate with QR code. Share it anywhere.',
    'featured_quizzes'                  => 'Featured quizzes',
    'see_all'                           => 'See all →',
    'no_quizzes_published'              => 'No quizzes yet.',
    'be_first_lecturer'                  => 'Be the first creator →',
    'quiz_questions'                    => 'questions',
    'quiz_min'                          => 'min',
    'quiz_attempts'                     => 'attempts',
    'quiz_avg'                          => 'Avg',
    'lecturer_teaser_students'           => 'Are you a teacher or tutor?',
    'lecturer_teaser_link_students'      => 'Share your knowledge →',

    // Home themes — professionals
    'professionals_hero_eyebrow'        => 'Verifiable certifications · career skills · LinkedIn-ready',
    'professionals_hero_h1'             => 'Prove Your Professional Skills.',
    'professionals_hero_sub'            => 'Industry-aligned quizzes across finance, tech, management, and more. Earn verifiable certificates with QR codes to showcase on LinkedIn and your CV.',
    'professionals_find_cert'           => 'Find Your Certification',
    'professionals_browse_domains'      => 'Browse Domains',
    'professionals_cert_title'          => 'Certificate of Completion',
    'hiw_step1_domain_title'            => 'Choose Your Domain',
    'hiw_step1_domain_desc'             => 'Finance, data science, project management, cybersecurity, HR, and more.',
    'hiw_step2_verify_title'            => 'Verify Your Knowledge',
    'hiw_step2_verify_desc'             => 'Timed, proctored assessments designed to test real-world professional skills.',
    'hiw_step3_share_title'             => 'Share Your Certificate',
    'hiw_step3_share_desc'              => 'Download a QR-verified PDF. Add it to your LinkedIn profile or CV in one click.',
    'professional_domains'              => 'Professional domains',
    'featured_certifications'           => 'Featured certifications',
    'no_certs_published'                => 'No certifications published yet.',
    'publish_first_cert'                => 'Publish the first one →',
    'lecturer_teaser_professionals'      => 'Are you a subject matter expert or L&D professional?',
    'lecturer_teaser_link_professionals' => 'Publish certification quizzes →',

    // Home themes — competition (hardcoded fallback label only)
    'no_quizzes_competition'            => 'No quizzes published yet.',
    'create_first_one'                  => 'Create the first one →',

    // Subscription checkout page
    'back_to_pricing'                   => 'Back to pricing',
    'subscribe_to'                      => 'Subscribe to :plan',
    'choose_billing_cycle'              => 'Choose your billing cycle below.',
    'billing_monthly'                   => 'Monthly',
    'billing_yearly'                    => 'Yearly',
    'billed_every_month'                => 'Billed every month',
    'save_badge'                        => 'Save',
    'billed_annually_per_mo'            => ':sym:mo/mo billed annually',
    'billed_annually_suffix'            => 'billed annually',
    'order_summary'                     => 'Order summary',
    'billing_label'                     => 'Billing',
    'total'                             => 'Total',
    'pay_with_razorpay'                 => 'Pay with Razorpay',
    'pay_with_stripe'                   => 'Pay with Stripe',
    'secure_cancel_note'                => 'Secure payment · Cancel anytime',

    // Subscription payment page
    'complete_your_payment'             => 'Complete your payment',

    // My Favourites page
    'saved_eyebrow'                     => 'Saved',
    'my_favourites_heading'             => 'My Favourites',
    'saved_on'                          => 'Saved :date',
    'view_quiz_btn'                     => 'View Quiz',
    'no_favourites_heading'             => 'No favourites yet',
    'no_favourites_body'                => 'Tap the heart icon on any quiz to save it here for later.',
    'browse_quizzes_btn'                => 'Browse Quizzes',

    // My Quizzes page
    'your_library'                      => 'Your library',
    'my_quizzes_heading'                => 'My Quizzes',
    'enrolled_on'                       => 'Enrolled :date',
    'last_attempt_result'               => 'Last attempt: :pct% — :status',
    'passed_label'                      => 'Passed',
    'failed_label'                      => 'Failed',
    'retake_btn'                        => 'Retake',
    'start_btn'                         => 'Start',
    'result_btn'                        => 'Result',
    'continue_btn'                      => 'Continue',
    'no_quizzes_heading'                => 'No quizzes yet',
    'no_quizzes_body'                   => 'Browse our library and enroll in a quiz to get started.',
    'discover_quizzes_btn'              => 'Discover quizzes',

    // Competition home theme
    'cmp_answered'                      => 'Answered',
    'cmp_unanswered'                    => 'Unanswered',
    'cmp_marked'                        => 'Marked',
    'cmp_view_all_quizzes'              => 'View all quizzes →',
    'cmp_featured_quizzes_fallback'     => 'Featured quizzes',

];
