<?php

namespace App\Filament\Lecturer\Resources;

use App\Exceptions\QuizNotAvailableException;
use App\Filament\Lecturer\Resources\QuizResource\Pages;
use App\Models\Category;
use App\Models\Quiz;
use App\Models\StudentBatch;
use App\Models\User;
use App\Services\Quiz\QuizAssignmentService;
use App\Services\Quiz\QuizPublishService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use OpenAI\Laravel\Facades\OpenAI;

class QuizResource extends Resource
{
    protected static ?string $model = Quiz::class;
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?int $navigationSort = 1;

    public static function getNavigationLabel(): string  { return __('lecturer.nav_quizzes'); }
    public static function getModelLabel(): string       { return __('lecturer.quiz_model_label'); }
    public static function getPluralModelLabel(): string { return __('lecturer.nav_quizzes'); }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('lecturer_id', auth()->id());
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Wizard::make([
                // Step 1: Basic Info
                Forms\Components\Wizard\Step::make(__('lecturer.step_basic_info'))
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label(__('lecturer.field_title'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn($state, Forms\Set $set) =>
                                $set('slug', Str::slug($state)))
                            ->hintIcon('heroicon-m-information-circle', __('lecturer.title_helper')),
                        Forms\Components\TextInput::make('slug')
                            ->label(__('lecturer.field_slug'))
                            ->required()
                            ->unique(Quiz::class, 'slug', ignoreRecord: true)
                            ->maxLength(255)
                            ->hintIcon('heroicon-m-information-circle', __('lecturer.slug_helper')),
                        Forms\Components\Select::make('category_id')
                            ->label(__('lecturer.field_category'))
                            ->options(Category::active()->pluck('name', 'id'))
                            ->searchable()
                            ->required()
                            ->hintIcon('heroicon-m-information-circle', __('lecturer.category_helper')),
                        Forms\Components\Textarea::make('description')
                            ->label(__('lecturer.field_description'))
                            ->rows(4)
                            ->columnSpanFull()
                            ->hintIcon('heroicon-m-information-circle', __('lecturer.description_helper'))
                            ->hintAction(
                                Forms\Components\Actions\Action::make('ai_description')
                                    ->label(__('lecturer.ai_generate_description'))
                                    ->icon('heroicon-o-sparkles')
                                    ->color('warning')
                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                        $title = trim($get('title') ?? '');
                                        if (! $title) {
                                            Notification::make()->title(__('lecturer.enter_title_first'))->warning()->send();
                                            return;
                                        }
                                        try {
                                            $response = OpenAI::chat()->create([
                                                'model' => 'gpt-4o',
                                                'messages' => [[
                                                    'role'    => 'user',
                                                    'content' => "Write a concise, engaging quiz description (2–3 sentences, no markdown) for a quiz titled: \"{$title}\". Focus on what learners will test and gain.",
                                                ]],
                                                'max_tokens' => 120,
                                            ]);
                                            $set('description', trim($response->choices[0]->message->content));
                                            Notification::make()->title(__('lecturer.description_generated'))->success()->send();
                                        } catch (\Throwable $e) {
                                            Notification::make()->title(__('lecturer.ai_error', ['message' => $e->getMessage()]))->danger()->send();
                                        }
                                    })
                            ),
                        Forms\Components\FileUpload::make('cover_image')
                            ->label(__('lecturer.field_cover_image'))
                            ->image()
                            ->imageEditor()
                            ->directory('quiz-covers')
                            ->columnSpanFull()
                            ->helperText(function (Forms\Get $get) {
                                $title = trim($get('title') ?? '');
                                return $title
                                    ? __('lecturer.cover_helper_with_title')
                                    : __('lecturer.cover_helper_no_title');
                            })
                            ->hintAction(
                                Forms\Components\Actions\Action::make('ai_cover')
                                    ->label(__('lecturer.ai_cover_label'))
                                    ->icon('heroicon-o-sparkles')
                                    ->color('warning')
                                    ->action(function (Forms\Get $get, Forms\Set $set) {
                                        $title = trim($get('title') ?? '');
                                        $desc  = trim($get('description') ?? '');
                                        if (! $title) {
                                            Notification::make()->title(__('lecturer.enter_title_first'))->warning()->send();
                                            return;
                                        }

                                        // ── Palette selection (driven by title) ──────────────────
                                        $palettes = [
                                            // [color1, color2, dark?]
                                            [[99,102,241],[139,92,246], false],   // 0  indigo→violet
                                            [[14,165,233],[99,102,241], false],   // 1  sky→indigo
                                            [[245,158,11],[239,68,68],  false],   // 2  amber→red
                                            [[16,185,129],[14,165,233], false],   // 3  emerald→sky
                                            [[236,72,153],[139,92,246], false],   // 4  pink→violet
                                            [[249,115,22],[245,158,11], false],   // 5  orange→amber
                                            [[20,184,166],[99,102,241], false],   // 6  teal→indigo
                                            [[15,23,42],[30,41,82],     true],    // 7  dark navy
                                            [[124,58,237],[219,39,119], false],   // 8  purple→fuchsia
                                            [[5,150,105],[6,182,212],   false],   // 9  green→cyan
                                            [[190,18,60],[234,88,12],   false],   // 10 crimson→orange
                                            [[31,41,55],[55,65,81],     true],    // 11 charcoal
                                        ];
                                        $pIdx = abs(crc32($title)) % count($palettes);
                                        [$rgb1, $rgb2, $isDark] = $palettes[$pIdx];

                                        // ── Layout variant (driven by description length/hash) ───
                                        $layoutSeed = abs(crc32($title . $desc));
                                        $layout = $layoutSeed % 5; // 0-4 distinct layouts

                                        $W = 1024; $H = 576; // 16:9
                                        $img = imagecreatetruecolor($W, $H);
                                        imagesavealpha($img, true);

                                        // ── Draw background gradient (direction varies by layout) ─
                                        for ($y = 0; $y < $H; $y++) {
                                            for ($x = 0; $x < $W; $x++) {
                                                $t = match($layout) {
                                                    0 => ($x / $W + $y / $H) / 2,            // diagonal ↘
                                                    1 => $x / $W,                             // horizontal →
                                                    2 => $y / $H,                             // vertical ↓
                                                    3 => (($W - $x) / $W + $y / $H) / 2,     // diagonal ↙
                                                    default => min(1, sqrt(($x/$W)**2 + ($y/$H)**2) / sqrt(2)), // radial
                                                };
                                                $r = (int)($rgb1[0] + ($rgb2[0]-$rgb1[0]) * $t);
                                                $g = (int)($rgb1[1] + ($rgb2[1]-$rgb1[1]) * $t);
                                                $b = (int)($rgb1[2] + ($rgb2[2]-$rgb1[2]) * $t);
                                                imagesetpixel($img, $x, $y, imagecolorallocate($img, $r, $g, $b));
                                            }
                                        }

                                        // ── Decorative shapes (vary by layout) ───────────────────
                                        $a1 = imagecolorallocatealpha($img, 255,255,255, 110); // ~14% white
                                        $a2 = imagecolorallocatealpha($img, 255,255,255, 120); // ~6% white
                                        $a3 = imagecolorallocatealpha($img, 0,0,0, 115);       // ~10% black

                                        switch ($layout) {
                                            case 0: // Large circle top-right + small bottom-left
                                                imagefilledellipse($img, $W - 60,  60, 420, 420, $a2);
                                                imagefilledellipse($img, 70,  $H - 50, 280, 280, $a1);
                                                break;
                                            case 1: // Two circles right side stacked
                                                imagefilledellipse($img, $W - 100, 160, 340, 340, $a2);
                                                imagefilledellipse($img, $W - 80,  460, 220, 220, $a1);
                                                break;
                                            case 2: // Horizontal band + circle
                                                imagefilledrectangle($img, 0, (int)($H*0.6), $W, $H, $a3);
                                                imagefilledellipse($img, (int)($W/2), (int)($H*0.3), 500, 300, $a2);
                                                break;
                                            case 3: // Three small circles scattered
                                                imagefilledellipse($img, 120, 100, 260, 260, $a2);
                                                imagefilledellipse($img, $W-140, $H-120, 300, 300, $a1);
                                                imagefilledellipse($img, (int)($W/2), (int)($H/2), 180, 180, $a2);
                                                break;
                                            default: // Diamond/rotated square (use polygon)
                                                $cx = (int)($W * 0.75); $cy = (int)($H * 0.35); $r = 200;
                                                imagefilledpolygon($img, [$cx,$cy-$r, $cx+$r,$cy, $cx,$cy+$r, $cx-$r,$cy], $a2);
                                                imagefilledellipse($img, (int)($W*0.2), (int)($H*0.75), 240, 240, $a1);
                                                break;
                                        }

                                        // ── Bottom bar ────────────────────────────────────────────
                                        $black20 = imagecolorallocatealpha($img, 0,0,0, 100);
                                        imagefilledrectangle($img, 0, $H - 58, $W, $H, $black20);

                                        $white   = imagecolorallocate($img, 255, 255, 255);
                                        $white70 = imagecolorallocatealpha($img, 255,255,255, 77);

                                        // ── Word-wrap + draw title ────────────────────────────────
                                        $font     = 5;
                                        $charW    = imagefontwidth($font);
                                        $charH    = imagefontheight($font);
                                        $maxChars = (int)(($W - 120) / $charW);
                                        $words    = explode(' ', strtoupper($title));
                                        $lines    = []; $cur = '';
                                        foreach ($words as $word) {
                                            $test = $cur === '' ? $word : $cur . ' ' . $word;
                                            if (strlen($test) > $maxChars && $cur !== '') { $lines[] = $cur; $cur = $word; }
                                            else { $cur = $test; }
                                        }
                                        if ($cur) $lines[] = $cur;
                                        $lines = array_slice($lines, 0, 4);

                                        $lineH   = $charH + 10;
                                        $totalTH = count($lines) * $lineH;
                                        $startY  = (int)(($H - $totalTH) / 2) - 10;

                                        foreach ($lines as $i => $line) {
                                            $lw = strlen($line) * $charW;
                                            $lx = (int)(($W - $lw) / 2);
                                            $ly = $startY + $i * $lineH;
                                            imagestring($img, $font, $lx+2, $ly+2, $line, imagecolorallocatealpha($img,0,0,0,90));
                                            imagestring($img, $font, $lx,   $ly,   $line, $white);
                                        }

                                        // ── Top label (category hint from first desc word or "QUIZ") ─
                                        $topLabel = 'QUIZ';
                                        if ($desc) {
                                            $firstWords = implode(' ', array_slice(explode(' ', strtoupper($desc)), 0, 3));
                                            if (strlen($firstWords) <= 24) $topLabel = $firstWords;
                                        }
                                        $lw = strlen($topLabel) * imagefontwidth(3);
                                        imagestring($img, 3, (int)(($W - $lw) / 2), 28, $topLabel, $white70);

                                        // ── Bottom brand ──────────────────────────────────────────
                                        $brand = 'Powered by ' . config('app.name');
                                        $bw = strlen($brand) * imagefontwidth(2);
                                        imagestring($img, 2, (int)(($W - $bw) / 2), $H - 42, $brand, $white70);

                                        // Save as PNG
                                        ob_start();
                                        imagepng($img);
                                        $png = ob_get_clean();
                                        imagedestroy($img);

                                        $filename = 'quiz-covers/ai-' . Str::uuid() . '.png';
                                        \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $png);
                                        $set('cover_image', [$filename]);
                                        Notification::make()->title(__('lecturer.cover_generated'))->success()->send();
                                    })
                            ),
                    ])->columns(2),

                // Step 2: Settings
                Forms\Components\Wizard\Step::make(__('lecturer.step_settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->afterValidation(function ($livewire) {
                        // Questions / bank import need a persisted quiz_id. On create,
                        // save a draft when leaving Settings and open Edit on Questions.
                        if ($livewire instanceof Pages\CreateQuiz) {
                            $livewire->saveDraftFromWizard();
                        }
                    })
                    ->schema([
                        Forms\Components\Section::make(__('lecturer.section_scheduling'))
                            ->description(__('lecturer.section_scheduling_desc'))
                            ->schema([
                            Forms\Components\TextInput::make('duration_minutes')
                                ->numeric()
                                ->label(__('lecturer.field_duration_minutes'))
                                ->placeholder(__('lecturer.duration_placeholder'))
                                ->hintIcon('heroicon-m-information-circle', __('lecturer.duration_minutes_helper')),
                            Forms\Components\TextInput::make('max_attempts')
                                ->numeric()
                                ->label(__('lecturer.field_max_attempts'))
                                ->placeholder(__('lecturer.max_attempts_placeholder'))
                                ->hintIcon('heroicon-m-information-circle', __('lecturer.max_attempts_helper')),
                        ])->columns(2),

                        Forms\Components\Section::make(__('lecturer.section_exam_options'))
                            ->description(__('lecturer.section_exam_options_desc'))
                            ->schema([
                            Forms\Components\TextInput::make('pass_percentage')
                                ->label(__('lecturer.field_pass_percentage'))
                                ->numeric()
                                ->default(60)
                                ->suffix('%')
                                ->minValue(1)
                                ->maxValue(100)
                                ->hintIcon('heroicon-m-information-circle', __('lecturer.pass_percentage_helper')),
                            Forms\Components\Select::make('visibility')
                                ->label(__('lecturer.field_visibility'))
                                ->options([
                                    'public'   => __('lecturer.visibility_public'),
                                    'private'  => __('lecturer.visibility_private'),
                                    'unlisted' => __('lecturer.visibility_unlisted'),
                                ])
                                ->default('public')
                                ->required()
                                ->hintIcon('heroicon-m-information-circle', __('lecturer.visibility_helper')),
                            Forms\Components\Toggle::make('shuffle_questions')
                                ->label(__('lecturer.field_shuffle_questions'))
                                ->default(false)
                                ->helperText(__('lecturer.shuffle_questions_helper')),
                            Forms\Components\Toggle::make('shuffle_options')
                                ->label(__('lecturer.field_shuffle_options'))
                                ->default(false)
                                ->helperText(__('lecturer.shuffle_options_helper')),
                            Forms\Components\Radio::make('results_release_mode')
                                ->label(__('lecturer.field_results_release'))
                                ->options([
                                    'immediate' => __('lecturer.results_release_immediate'),
                                    'held'      => __('lecturer.results_release_held'),
                                ])
                                ->descriptions([
                                    'immediate' => __('lecturer.show_result_helper'),
                                    'held'      => __('lecturer.hold_results_helper'),
                                ])
                                ->default('immediate')
                                ->required()
                                ->dehydrated(false)
                                ->columnSpanFull(),
                            Forms\Components\Toggle::make('allow_review_after_submit')
                                ->label(__('lecturer.field_allow_review'))
                                ->default(true)
                                ->helperText(__('lecturer.allow_review_helper')),
                            Forms\Components\Toggle::make('negative_marking_enabled')
                                ->label(__('lecturer.field_negative_marking'))
                                ->default(false)
                                ->helperText(__('lecturer.negative_marking_helper')),
                            Forms\Components\Toggle::make('certificate_enabled')
                                ->label(__('lecturer.field_certificate'))
                                ->default(false)
                                ->live()
                                ->helperText(__('lecturer.certificate_helper')),
                            \App\Filament\Lecturer\Forms\Components\CertificateStylePicker::make('certificate_template')
                                ->label(__('lecturer.field_certificate_style'))
                                ->default('classic')
                                ->visible(fn (Forms\Get $get) => (bool) $get('certificate_enabled'))
                                ->hintIcon('heroicon-m-information-circle', __('lecturer.certificate_style_helper'))
                                ->columnSpanFull(),
                        ])->columns(2),
                    ]),

                // Step 3: Questions (inline manager with AI gen + bank)
                Forms\Components\Wizard\Step::make(__('lecturer.step_questions'))
                    ->icon('heroicon-o-question-mark-circle')
                    ->schema([
                        Forms\Components\View::make('filament.lecturer.partials.quiz-questions-step')
                            ->columnSpanFull(),
                    ]),

                // Step 4: Review & Publish
                Forms\Components\Wizard\Step::make(__('lecturer.step_review_publish'))
                    ->icon('heroicon-o-rocket-launch')
                    ->schema([
                        Forms\Components\Section::make(__('lecturer.section_publish_settings'))->schema([
                            Forms\Components\Placeholder::make('batch_exam_workflow')
                                ->label(__('lecturer.batch_exam_workflow_heading'))
                                ->content(__('lecturer.batch_exam_workflow_body'))
                                ->columnSpanFull(),

                            Forms\Components\Select::make('status')
                                ->label(__('lecturer.field_status'))
                                ->options([
                                    'draft'     => __('lecturer.status_draft_option'),
                                    'published' => __('lecturer.status_published_option'),
                                    'scheduled' => __('lecturer.status_scheduled_option'),
                                ])
                                ->default('draft')
                                ->required()
                                ->live()
                                ->helperText(fn (Forms\Get $get) => match($get('status')) {
                                    'published' => __('lecturer.status_helper_published'),
                                    'scheduled' => __('lecturer.status_helper_scheduled'),
                                    default     => __('lecturer.status_helper_draft'),
                                }),

                            Forms\Components\DateTimePicker::make('start_at')
                                ->label(fn (Forms\Get $get) => $get('status') === 'scheduled'
                                    ? __('lecturer.field_publish_open_at')
                                    : __('lecturer.field_exam_opens'))
                                ->helperText(fn (Forms\Get $get) => $get('status') === 'scheduled'
                                    ? __('lecturer.start_at_helper_scheduled')
                                    : __('lecturer.field_exam_opens_helper'))
                                ->required(fn (Forms\Get $get) => $get('status') === 'scheduled')
                                ->after('now'),

                            Forms\Components\DateTimePicker::make('end_at')
                                ->label(__('lecturer.field_exam_closes'))
                                ->helperText(__('lecturer.field_exam_closes_helper'))
                                ->after('start_at')
                                ->live(),

                            Forms\Components\Toggle::make('force_submit_at_end')
                                ->label(__('lecturer.field_force_submit_at_end'))
                                ->helperText(__('lecturer.field_force_submit_at_end_helper'))
                                ->default(false)
                                ->visible(fn (Forms\Get $get) => filled($get('end_at'))),
                        ])->columns(1),

                        Forms\Components\Section::make(__('lecturer.section_seo'))->schema([
                            Forms\Components\Textarea::make('meta_description')
                                ->label(__('lecturer.field_meta_description'))
                                ->rows(3)
                                ->maxLength(300)
                                ->columnSpanFull()
                                ->helperText(__('lecturer.meta_description_helper'))
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('ai_seo_desc')
                                        ->label(__('lecturer.ai_generate_description'))
                                        ->icon('heroicon-o-sparkles')
                                        ->color('warning')
                                        ->action(function (Forms\Get $get, Forms\Set $set) {
                                            $title = trim($get('title') ?? '');
                                            $desc  = trim($get('description') ?? '');
                                            if (! $title) {
                                                Notification::make()->title(__('lecturer.seo_no_title_warning'))->warning()->send();
                                                return;
                                            }
                                            try {
                                                $context = $title . ($desc ? ": $desc" : '');
                                                $response = OpenAI::chat()->create([
                                                    'model'    => 'gpt-4o',
                                                    'messages' => [[
                                                        'role'    => 'user',
                                                        'content' => "Write a compelling SEO meta description (max 155 characters, no markdown, no quotes) for a quiz titled: \"{$context}\". Focus on what learners will gain and why they should take it.",
                                                    ]],
                                                    'max_tokens' => 80,
                                                ]);
                                                $set('meta_description', trim($response->choices[0]->message->content));
                                                Notification::make()->title(__('lecturer.seo_desc_generated'))->success()->send();
                                            } catch (\Throwable $e) {
                                                Notification::make()->title(__('lecturer.ai_error', ['message' => $e->getMessage()]))->danger()->send();
                                            }
                                        })
                                ),

                            Forms\Components\TextInput::make('meta_keywords')
                                ->label(__('lecturer.field_meta_keywords'))
                                ->columnSpanFull()
                                ->helperText(__('lecturer.meta_keywords_helper'))
                                ->hintAction(
                                    Forms\Components\Actions\Action::make('ai_seo_kw')
                                        ->label(__('lecturer.ai_generate_description'))
                                        ->icon('heroicon-o-sparkles')
                                        ->color('warning')
                                        ->action(function (Forms\Get $get, Forms\Set $set) {
                                            $title    = trim($get('title') ?? '');
                                            $desc     = trim($get('description') ?? '');
                                            $category = trim($get('category_id') ?? '');
                                            if (! $title) {
                                                Notification::make()->title(__('lecturer.seo_no_title_warning'))->warning()->send();
                                                return;
                                            }
                                            try {
                                                $catName = $category
                                                    ? (\App\Models\Category::find($category)?->name ?? '')
                                                    : '';
                                                $context = $title . ($desc ? " — $desc" : '') . ($catName ? " (Category: $catName)" : '');
                                                $response = OpenAI::chat()->create([
                                                    'model'    => 'gpt-4o',
                                                    'messages' => [[
                                                        'role'    => 'user',
                                                        'content' => "Generate 8–12 SEO keywords (comma-separated, lowercase, no markdown, no extra text) for a quiz about: \"{$context}\". Include topic keywords, skill level, and quiz/test/exam variants.",
                                                    ]],
                                                    'max_tokens' => 80,
                                                ]);
                                                $set('meta_keywords', trim($response->choices[0]->message->content, " ,\n"));
                                                Notification::make()->title(__('lecturer.seo_kw_generated'))->success()->send();
                                            } catch (\Throwable $e) {
                                                Notification::make()->title(__('lecturer.ai_error', ['message' => $e->getMessage()]))->danger()->send();
                                            }
                                        })
                                ),
                        ])->columns(1),
                    ]),
            ])
                ->columnSpanFull()
                // Create must not skip steps — afterValidation (draft save) only runs
                // when Next is used. Edit can skip freely between steps.
                ->skippable(fn ($livewire) => ! ($livewire instanceof Pages\CreateQuiz))
                ->persistStepInQueryString('step'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->label('')
                    ->circular()
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->title).'&background=6C2E63&color=fff'),
                Tables\Columns\TextColumn::make('title')
                    ->label(__('lecturer.col_title'))
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('category.name')
                    ->label(__('lecturer.col_category'))
                    ->badge()
                    ->color('gray'),
                Tables\Columns\TextColumn::make('total_questions')
                    ->label(__('lecturer.col_questions'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->label(__('lecturer.col_status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state) => __('lecturer.status_' . $state))
                    ->color(fn(string $state) => match($state) {
                        'draft'     => 'warning',
                        'published' => 'success',
                        'archived'  => 'danger',
                        'scheduled' => 'primary',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('total_attempts')
                    ->label(__('lecturer.col_attempts'))
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('lecturer.col_created_at'))
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(__('lecturer.col_status'))
                    ->options([
                        'draft'     => __('lecturer.status_draft'),
                        'published' => __('lecturer.status_published'),
                        'archived'  => __('lecturer.status_archived'),
                        'scheduled' => __('lecturer.status_scheduled'),
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('manage_questions')
                    ->label(__('lecturer.action_manage_questions'))
                    ->icon('heroicon-o-list-bullet')
                    ->url(fn(Quiz $record) => static::getUrl('questions', ['record' => $record])),
                Tables\Actions\Action::make('publish')
                    ->label(__('lecturer.action_publish'))
                    ->icon('heroicon-o-rocket-launch')
                    ->color('success')
                    ->visible(fn(Quiz $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (Quiz $record) {
                        try {
                            app(QuizPublishService::class)->publish($record);
                        } catch (QuizNotAvailableException $e) {
                            Notification::make()->title($e->getMessage())->danger()->send();
                        }
                    }),
                Tables\Actions\Action::make('assign')
                    ->label(__('lecturer.action_assign_exam'))
                    ->icon('heroicon-o-user-group')
                    ->color('info')
                    ->visible(fn (Quiz $record) => $record->status === 'published')
                    ->form([
                        Forms\Components\Select::make('student_batch_id')
                            ->label(__('lecturer.assign_field_batch'))
                            ->options(fn () => StudentBatch::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->nullable()
                            ->helperText(__('lecturer.assign_batch_helper')),
                        Forms\Components\Select::make('student_ids')
                            ->label(__('lecturer.assign_field_students'))
                            ->options(fn () => User::query()->where('role', 'student')->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                            ->multiple()
                            ->searchable()
                            ->nullable()
                            ->helperText(__('lecturer.assign_students_helper')),
                    ])
                    ->action(function (Quiz $record, array $data) {
                        $service = app(QuizAssignmentService::class);
                        $lecturer = auth()->user();
                        $assigned = 0;

                        if (! empty($data['student_batch_id'])) {
                            $batch = StudentBatch::find($data['student_batch_id']);
                            if ($batch) {
                                $service->assignToBatch($record, $lecturer, $batch);
                                $assigned++;
                            }
                        }

                        foreach ($data['student_ids'] ?? [] as $studentId) {
                            $student = User::find($studentId);
                            if ($student) {
                                $service->assignToStudent($record, $lecturer, $student);
                                $assigned++;
                            }
                        }

                        if ($assigned === 0) {
                            Notification::make()
                                ->title(__('lecturer.assign_nothing_selected'))
                                ->warning()
                                ->send();

                            return;
                        }

                        Notification::make()
                            ->title(__('lecturer.assign_success'))
                            ->success()
                            ->send();
                    }),
                Tables\Actions\Action::make('archive')
                    ->label(__('lecturer.action_archive'))
                    ->icon('heroicon-o-archive-box')
                    ->color('danger')
                    ->visible(fn(Quiz $record) => $record->status === 'published')
                    ->requiresConfirmation()
                    ->action(fn(Quiz $record) => $record->update(['status' => 'archived'])),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'     => Pages\ListQuizzes::route('/'),
            'create'    => Pages\CreateQuiz::route('/create'),
            'edit'      => Pages\EditQuiz::route('/{record}/edit'),
            'questions' => Pages\ManageQuestions::route('/{record}/questions'),
        ];
    }

    public static function resultsReleaseModeFromQuiz(Quiz $quiz): string
    {
        if ($quiz->hold_results_until_published) {
            return 'held';
        }

        return 'immediate';
    }

    public static function applyResultsReleaseMode(array &$data, ?string $mode = null): void
    {
        $mode = $mode ?? ($data['results_release_mode'] ?? 'immediate');

        if ($mode === 'held') {
            $data['show_result_immediately']      = false;
            $data['hold_results_until_published'] = true;
        } else {
            $data['show_result_immediately']      = true;
            $data['hold_results_until_published'] = false;
        }

        unset($data['results_release_mode']);
    }
}
