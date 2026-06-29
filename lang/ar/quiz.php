<?php

return [

    // Discovery / listing page
    'discover_title'           => 'Discover Exams',
    'discover_subtitle'        => 'Browse exams created by experts and AI.',
    'search_placeholder'       => 'Search exams…',
    'filters'                  => 'Filters',
    'search'                   => 'Search',
    'filter_category'          => 'Category',
    'search_categories'        => 'Search categories…',
    'filter_all_categories'    => 'All Categories',
    'no_categories_found'      => 'No categories found',
    'filter_price'             => 'Price',
    'filter_all'               => 'All',
    'filter_all_prices'        => 'All Prices',
    'filter_free'              => 'Free',
    'filter_paid'              => 'Paid',
    'sort_by'                  => 'Sort by',
    'sort_newest'              => 'Newest First',
    'sort_popular'             => 'Most Popular',
    'sort_price_asc'           => 'Price: Low → High',
    'sort_price_desc'          => 'Price: High → Low',
    'clear_all_filters'        => 'Clear all filters',
    'clear_filters'            => 'Clear filters',
    'no_results'               => 'No exams found',
    'no_results_desc'          => 'Try different keywords or filters.',

    // Quiz card
    'card_free'                => 'Free',
    'card_questions'           => ':count question|:count questions',
    'card_attempts'            => ':count attempt|:count attempts',
    'card_avg_score'           => 'Avg. :score%',
    'card_by'                  => 'by :name',

    // Quiz detail / show
    'whats_in_this_quiz'       => "What's in this exam",
    'label_questions'          => 'Questions',
    'label_duration'           => 'Duration',
    'label_duration_minutes'   => ':count minutes',
    'label_no_time_limit'      => 'No time limit',
    'label_attempts_allowed'   => 'Attempts allowed',
    'label_attempts_unlimited' => 'Unlimited',
    'label_attempts_per_purchase' => ':count per purchase',
    'label_negative_marking'   => 'Negative marking',
    'label_negative_enabled'   => 'Enabled',
    'label_negative_disabled'  => 'Disabled',
    'label_pass_at'            => 'Pass at',
    'label_total_marks'        => 'Total marks',
    'label_category'           => 'Category',
    'label_difficulty'         => 'Difficulty',

    // Sample question section
    'sample_question'          => 'Sample question',
    'sample_question_pill'     => 'Question 1 of :total · :marks mark',
    'unlock_all_questions'     => 'Enroll to unlock all :count questions',

    // CTA buttons
    'start_exam'               => 'Start exam →',
    'enroll_start_exam'        => 'Enroll & start exam',
    'buy_for_price'            => 'Buy for :symbol:price →',
    'signup_to_enroll'         => 'Sign up to enroll',
    'signup_to_buy'            => 'Sign up to buy',
    'resume_attempt'           => 'Resume attempt →',
    'attempt_again'            => 'Attempt again',
    'view_result'              => 'View result →',
    'max_attempts_reached'     => 'Maximum attempts reached',
    'not_enrolled'             => 'Not enrolled',

    // Countdown
    'countdown_starts_in'      => 'Starts in',
    'countdown_opens'          => 'Opens :datetime',
    'countdown_ends_in'        => 'Ends in',
    'countdown_closes'         => 'Closes :datetime',
    'countdown_ended'          => 'Exam ended',
    'countdown_ended_at'       => 'Ended :datetime',
    'countdown_days'           => 'Days',
    'countdown_hours'          => 'Hours',
    'countdown_minutes'        => 'Minutes',
    'countdown_seconds'        => 'Seconds',

    // Leaderboard
    'top_scores'               => 'Top scores',
    'anonymous'                => 'Anonymous',
    'rank'                     => 'Rank',
    'score'                    => 'Score',

    // Certificate
    'certificate_verified'     => 'Verified Certificate',
    'certificate_pass_earn'    => 'Pass & earn a shareable certificate with QR verification',

    // Share
    'save_to_favourites'       => 'Save to favourites',
    'remove_from_favourites'   => 'Remove from favourites',
    'share_this_quiz'          => 'Share this exam',
    'copy_link'                => 'Copy link',
    'link_copied'              => 'Link copied!',

    // Creator card on quiz detail
    'lecturer_quizzes_published'  => ':count exams published',
    'more_by_lecturer'            => 'More by this creator',

    // Attempt / result page
    'result_heading'           => 'Exam Result',
    'result_score'             => 'Your score',
    'result_passed'            => 'Passed!',
    'result_failed'            => 'Not passed',
    'result_percentage'        => ':percent%',
    'result_time_taken'        => 'Time taken',
    'result_questions_correct' => 'Correct',
    'result_questions_wrong'   => 'Wrong',
    'result_questions_skipped' => 'Skipped',
    'result_marks_earned'      => 'Marks earned',
    'result_total_marks'       => 'out of :total',
    'result_pass_percentage'   => 'Pass percentage',
    'result_download_cert'     => 'Download certificate',
    'result_review_answers'    => 'Review answers',
    'result_try_again'         => 'Try again',
    'result_back_to_quiz'      => 'Back to exam',

    // My attempts page
    'my_attempts_title'        => 'My Attempts',
    'my_attempts_empty'        => 'No attempts yet.',
    'my_attempts_empty_desc'   => 'Browse exams and take your first exam.',
    'attempt_number'           => 'Attempt #:number',
    'attempt_date'             => 'Taken :date',
    'attempt_in_progress'      => 'In progress',
    'attempt_completed'        => 'Completed',
    'attempt_timed_out'        => 'Timed out',

    // My dashboard
    'dashboard_title'          => 'My Dashboard',
    'dashboard_total_attempts' => 'Total attempts',
    'dashboard_passed'         => 'Passed',
    'dashboard_certificates'   => 'Certificates',
    'dashboard_avg_score'      => 'Average score',

    // My certificates
    'certificates_title'       => 'My Certificates',
    'certificates_empty'       => 'No certificates yet.',
    'certificates_empty_desc'  => 'Pass an exam with certificate enabled to earn one.',
    'certificate_download'     => 'Download',
    'certificate_verify_url'   => 'Verify',
    'certificate_issued'       => 'Issued :date',

    // Checkout
    'checkout_title'           => 'Complete your purchase',
    'checkout_order_summary'   => 'Order summary',
    'checkout_quiz'            => 'Exam',
    'checkout_price'           => 'Price',
    'checkout_total'           => 'Total',
    'checkout_pay_with'        => 'Pay with',
    'checkout_pay_razorpay'    => 'Pay with Razorpay',
    'checkout_pay_stripe'      => 'Pay with Card',
    'checkout_processing'      => 'Processing payment…',
    'checkout_success'         => 'Payment successful! You are now enrolled.',
    'checkout_failed'          => 'Payment failed. Please try again.',

    // For creators page
    'fc_eyebrow'               => 'AI-powered · free to start · earn from every exam sale',
    'fc_hero_h1'               => 'Turn your expertise into a passive income stream.',
    'fc_hero_sub'              => 'Create AI-powered exams in minutes. Publish once. Earn every time someone buys — while you sleep, teach, or take a break.',
    'fc_hero_cta_primary'      => 'Start creating free',
    'fc_hero_cta_secondary'    => 'See pricing',
    'fc_hiw_title'             => 'Start earning in 4 steps',
    'fc_hiw_step1_title'       => 'Register as Creator',
    'fc_hiw_step1_desc'        => 'Free account. No credit card. Takes 30 seconds.',
    'fc_hiw_step2_title'       => 'Generate with AI',
    'fc_hiw_step2_desc'        => 'Describe your topic. AI writes the questions. You review.',
    'fc_hiw_step3_title'       => 'Set Price & Publish',
    'fc_hiw_step3_desc'        => 'Free or paid. Scheduled or always-on. One click to go live.',
    'fc_hiw_step4_title'       => 'Collect Earnings',
    'fc_hiw_step4_desc'        => 'Track attempts, see your score distribution, request a payout when ready.',
    'fc_cta_h2'                => 'Ready to start earning?',
    'fc_cta_sub'               => 'Join educators, coaches, and subject matter experts already selling on :app.',
    'fc_cta_btn'               => 'Create your first exam free',
    'fc_compact_features_header' => 'Plus everything else you need',
    'fc_pricing_sub'           => "Start free. Upgrade when you're ready to scale.",
    'fc_view_full_pricing'     => 'View full pricing details →',

    // Feature row 1 — AI Generation
    'fc_feat1_label'           => 'AI Question Generation',
    'fc_feat1_h2'              => 'Describe a topic. Get 50 questions in seconds.',
    'fc_feat1_body'            => 'Type a prompt — topic, difficulty, question count, negative marking value. GPT-4o writes the questions, options, correct answers, and explanations. You review and publish. The whole thing takes under 5 minutes.',
    'fc_feat1_b1'              => 'MCQ, true/false, fill-in-the-blank — all types supported',
    'fc_feat1_b2'              => 'Up to 50 questions per prompt',
    'fc_feat1_b3'              => 'Marks and negative marks per question',
    'fc_feat1_b4'              => 'Explanations auto-generated for every answer',
    'fc_ai_generator_title'    => 'AI Question Generator',
    'fc_ai_prompt_label'       => 'Topic',
    'fc_ai_generated_badge'    => '3 of 15 generated',
    'fc_generating_questions'  => 'Generating questions…',

    // Feature row 2 — IBPS Exam Interface
    'fc_feat2_label'           => 'Professional Exam Interface',
    'fc_feat2_h2'              => 'Your students get a professional exam experience.',
    'fc_feat2_body'            => 'Every exam you publish runs in our IBPS-style exam panel — numbered question palette, colour-coded status (answered/skipped/marked), countdown timer, and easy navigation. Designed to feel familiar to students who have taken competitive exams.',
    'fc_feat2_b1'              => 'Numbered question palette with colour coding',
    'fc_feat2_b2'              => 'Per-question and overall timers',
    'fc_feat2_b3'              => 'Mark for review, save & next, submit exam',
    'fc_feat2_b4'              => 'Proctoring mode with tab-switch detection',

    // Feature row 3 — Monetization
    'fc_feat3_label'           => 'Monetization',
    'fc_feat3_h2'              => 'Set your price. Earn from every sale.',
    'fc_feat3_body'            => 'Free exams build your audience. Paid exams build your income. Set any price — the platform takes a small commission, you keep the rest. Request a payout to your bank or UPI at any time (processed by the platform admin).',
    'fc_feat3_b1'              => 'Per-exam pricing — customers pay once for access',
    'fc_feat3_b2'              => 'Mix free and paid exams in your portfolio',
    'fc_feat3_b3'              => 'Real-time earnings dashboard',
    'fc_feat3_b4'              => 'Request payout to bank, UPI, or PayPal',

    // Earnings mockup
    'fc_earnings_this_month'   => 'Earnings this month',
    'fc_stat_attempts'         => 'Attempts',
    'fc_stat_avg_sale'         => 'Avg/sale',
    'fc_stat_pass_rate'        => 'Pass rate',

    // Compact feature checklist
    'fc_cf_item1'              => 'Auto-certificates with QR verification',
    'fc_cf_item2'              => 'Negative marking + per-question marks',
    'fc_cf_item3'              => 'Scheduled exams with start/end datetime',
    'fc_cf_item4'              => 'Per-question analytics and drop-off rates',
    'fc_cf_item5'              => 'Multiple attempts with configurable limits',
    'fc_cf_item6'              => 'Per-question timers and question shuffle',
    'fc_cf_item7'              => 'Proctoring — detect tab switching',
    'fc_cf_item8'              => 'Free + paid exam mix in same account',

];
