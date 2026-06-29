@php
$presets = [
    'preset_default' => ['label' => 'Default',        'emoji' => '🟣', 'ring' => '#6C2E63', 'primary' => '#6C2E63', 'accent' => '#E0A431', 'font_display' => 'Plus Jakarta Sans', 'font_primary' => 'Inter',         'font_size_base' => '16px'],
    'preset_ocean'   => ['label' => 'Ocean Pro',       'emoji' => '🔵', 'ring' => '#0369a1', 'primary' => '#0369a1', 'accent' => '#f59e0b', 'font_display' => 'Sora',              'font_primary' => 'DM Sans',        'font_size_base' => '16px'],
    'preset_forest'  => ['label' => 'Forest Academy',  'emoji' => '🟢', 'ring' => '#166534', 'primary' => '#166534', 'accent' => '#ca8a04', 'font_display' => 'Merriweather',      'font_primary' => 'Source Sans 3',  'font_size_base' => '17px'],
    'preset_slate'   => ['label' => 'Slate Corporate', 'emoji' => '⚫', 'ring' => '#1e293b', 'primary' => '#1e293b', 'accent' => '#6366f1', 'font_display' => 'Space Grotesk',     'font_primary' => 'IBM Plex Sans',  'font_size_base' => '15px'],
    'preset_rose'    => ['label' => 'Rose Modern',     'emoji' => '🌸', 'ring' => '#9d174d', 'primary' => '#9d174d', 'accent' => '#0ea5e9', 'font_display' => 'Nunito',            'font_primary' => 'Nunito',         'font_size_base' => '16px'],
    'preset_amber'   => ['label' => 'Amber Edu',       'emoji' => '🟡', 'ring' => '#b45309', 'primary' => '#b45309', 'accent' => '#7c3aed', 'font_display' => 'Poppins',           'font_primary' => 'Poppins',        'font_size_base' => '16px'],
];
@endphp

<div
    x-data="{
        active: $wire.data.active_preset ?? '',
        pick(key, primary, accent, fontDisplay, fontPrimary, fontSizeBase) {
            this.active = key;
            $wire.set('data.active_preset',   key);
            $wire.set('data.primary_color',   primary);
            $wire.set('data.accent_color',    accent);
            $wire.set('data.font_display',    fontDisplay);
            $wire.set('data.font_primary',    fontPrimary);
            $wire.set('data.font_size_base',  fontSizeBase);
        }
    }"
    class="grid gap-3"
    style="grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));"
>
    @foreach($presets as $key => $p)
    <button
        type="button"
        @click="pick(
            '{{ $key }}',
            '{{ $p['primary'] }}',
            '{{ $p['accent'] }}',
            '{{ $p['font_display'] }}',
            '{{ $p['font_primary'] }}',
            '{{ $p['font_size_base'] }}'
        )"
        :class="active === '{{ $key }}'
            ? 'preset-card preset-card--active'
            : 'preset-card'"
        :style="active === '{{ $key }}'
            ? '--preset-ring: {{ $p['ring'] }}'
            : ''"
    >
        <span class="preset-card__emoji">{{ $p['emoji'] }}</span>
        <span class="preset-card__label">{{ $p['label'] }}</span>
        <span class="preset-card__check" x-show="active === '{{ $key }}'">✓</span>
    </button>
    @endforeach
</div>

<style>
.preset-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 14px 10px;
    border-radius: 12px;
    border: 2px solid #e5e7eb;
    background: #fff;
    cursor: pointer;
    transition: border-color .15s, box-shadow .15s, background .15s;
    position: relative;
    font-family: inherit;
}
.preset-card:hover {
    border-color: #a3a3a3;
    background: #f9fafb;
}
.preset-card--active {
    border-color: var(--preset-ring, #6366f1) !important;
    background: color-mix(in srgb, var(--preset-ring, #6366f1) 8%, white) !important;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--preset-ring, #6366f1) 20%, transparent);
}
.preset-card__emoji { font-size: 22px; line-height: 1; }
.preset-card__label { font-size: 12px; font-weight: 600; color: #374151; text-align: center; line-height: 1.3; }
.preset-card__check {
    position: absolute;
    top: 6px; right: 8px;
    font-size: 11px;
    font-weight: 700;
    color: var(--preset-ring, #6366f1);
}
</style>
