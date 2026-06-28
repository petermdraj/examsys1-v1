<?php

namespace App\Support;

class CategoryIcons
{
    /**
     * All available category icons.
     * Key = stored in DB, value = [label, svg path data]
     */
    public static function all(): array
    {
        return [
            'laptop'      => ['label' => 'Laptop / Technology',   'path' => 'M4 6a2 2 0 012-2h12a2 2 0 012 2v7a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM2 20h20M8 17v3M16 17v3'],
            'code'        => ['label' => 'Code / Programming',    'path' => 'M8 9l-4 4 4 4M16 9l4 4-4 4M12 5l-2 14'],
            'beaker'      => ['label' => 'Beaker / Science',      'path' => 'M9 3h6M9 3v7l-4 9a1 1 0 00.9 1.4h12.2A1 1 0 0025 19l-4-9V3'],
            'atom'        => ['label' => 'Atom / Physics',        'path' => 'M12 12m-1 0a1 1 0 102 0 1 1 0 10-2 0M12 5.5C8 5.5 4.5 8.5 4.5 12S8 18.5 12 18.5 19.5 15.5 19.5 12 16 5.5 12 5.5zM5.5 8.5C7 6 9 4.5 12 4.5c3 0 5 1.5 6.5 4M5.5 15.5C7 18 9 19.5 12 19.5c3 0 5-1.5 6.5-4'],
            'calculator'  => ['label' => 'Calculator / Maths',    'path' => 'M6 3h12a1 1 0 011 1v16a1 1 0 01-1 1H6a1 1 0 01-1-1V4a1 1 0 011-1zM9 7h6M9 12h2M13 12h2M9 16h2M13 16h2'],
            'book'        => ['label' => 'Book / History',        'path' => 'M4 19.5A2.5 2.5 0 016.5 17H20M4 19.5A2.5 2.5 0 006.5 22H20V2H6.5A2.5 2.5 0 004 4.5v15z'],
            'globe'       => ['label' => 'Globe / Geography',     'path' => 'M12 2a10 10 0 100 20A10 10 0 0012 2zM2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z'],
            'chat'        => ['label' => 'Chat / Language',       'path' => 'M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z'],
            'bulb'        => ['label' => 'Bulb / General Know.',  'path' => 'M12 2a7 7 0 00-4 12.74V17a1 1 0 001 1h6a1 1 0 001-1v-2.26A7 7 0 0012 2zM9 21h6M10 17v-3H8l4-8 4 8h-2v3'],
            'chart'       => ['label' => 'Chart / Business',      'path' => 'M3 3v18h18M7 16l4-4 4 4 4-8'],
            'currency'    => ['label' => 'Currency / Finance',    'path' => 'M12 1v22M17 5H9.5a3.5 3.5 0 100 7h5a3.5 3.5 0 110 7H6'],
            'heart'       => ['label' => 'Heart / Health',        'path' => 'M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z'],
            'music'       => ['label' => 'Music / Arts',          'path' => 'M9 18V5l12-2v13M9 18a3 3 0 01-6 0 3 3 0 016 0zM21 16a3 3 0 01-6 0 3 3 0 016 0z'],
            'palette'     => ['label' => 'Palette / Culture',     'path' => 'M12 2C6.48 2 2 6.48 2 12c0 5.52 4.48 10 10 10 1.1 0 2-.9 2-2v-.5c0-.55-.22-1.05-.59-1.41a.996.996 0 01-.01-1.42C13.69 16.26 14 15.66 14 15c0-1.66-1.34-3-3-3s-3 1.34-3 3c0 .66.31 1.26.6 1.67.38.37.6.87.6 1.42V18a2 2 0 002 2c4.42 0 8-3.58 8-8S17.52 2 12 2zM7 13a1 1 0 110-2 1 1 0 010 2zm2-4a1 1 0 110-2 1 1 0 010 2zm6 0a1 1 0 110-2 1 1 0 010 2zm2 4a1 1 0 110-2 1 1 0 010 2z'],
            'trophy'      => ['label' => 'Trophy / Sports',       'path' => 'M6 9H3V4h18v5h-3M6 9a6 6 0 006 6 6 6 0 006-6M8 21h8M12 15v6'],
            'scale'       => ['label' => 'Scale / Law',           'path' => 'M12 3v18M3 6l9-3 9 3M6 12H3l3-6M18 12h3l-3-6M6 12a3 3 0 006 0M18 12a3 3 0 01-6 0'],
            'leaf'        => ['label' => 'Leaf / Environment',    'path' => 'M17 8C8 10 5.9 16.17 3.82 22M21 3C13.12 3 6 8.5 5 15c4-3 11-4 16-2'],
            'puzzle'      => ['label' => 'Puzzle / Aptitude',     'path' => 'M11 4a1 1 0 00-2 0v1H7a2 2 0 00-2 2v2H4a1 1 0 000 2h1v2H4a1 1 0 000 2h1v2a2 2 0 002 2h2v1a1 1 0 002 0v-1h2v1a1 1 0 002 0v-1h2a2 2 0 002-2v-2h1a1 1 0 000-2h-1v-2h1a1 1 0 000-2h-1V7a2 2 0 00-2-2h-2V4a1 1 0 00-2 0v1h-2V4z'],
            'newspaper'   => ['label' => 'Newspaper / Affairs',   'path' => 'M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2zM16 2v4M8 2v4M3 10h18M8 14h8M8 17h4'],
            'cog'         => ['label' => 'Cog / Engineering',     'path' => 'M12 15a3 3 0 100-6 3 3 0 000 6zM19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 012.83-2.83l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z'],
            'brain'       => ['label' => 'Brain / Psychology',    'path' => 'M9.5 2A6.5 6.5 0 003 8.5c0 2.5 1.5 4.5 3 5.5v5h3v-2h1v2h3v-5c1.5-1 3-3 3-5.5A6.5 6.5 0 009.5 2zM9.5 2c2 1 3.5 3 3.5 6'],
            'trending'    => ['label' => 'Trending / Economics',  'path' => 'M23 6l-9.5 9.5-5-5L1 18M23 6h-6M23 6v6'],
            'clipboard'   => ['label' => 'Clipboard / Exams',     'path' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2M9 12l2 2 4-4'],
        ];
    }

    public static function svg(string $key, int $size = 28, string $stroke = 'currentColor'): string
    {
        $icons = self::all();
        if (! isset($icons[$key])) {
            return '';
        }
        $d = htmlspecialchars($icons[$key]['path'], ENT_QUOTES, 'UTF-8');
        $stroke = htmlspecialchars($stroke, ENT_QUOTES, 'UTF-8');

        return '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="' . $stroke . '" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="' . $d . '"/></svg>';
    }

    public static function selectOptions(): array
    {
        return collect(self::all())
            ->map(fn($v, $k) => $v['label'])
            ->toArray();
    }

    /** Best-guess key from a stored heroicon name or raw key */
    public static function resolveKey(?string $stored): string
    {
        if (!$stored) return 'book';
        // Already a known key
        if (isset(self::all()[$stored])) return $stored;
        // Parse heroicon name → keyword match
        $map = [
            'cpu' => 'laptop', 'computer' => 'laptop', 'device' => 'laptop',
            'code' => 'code', 'bracket' => 'code',
            'beaker' => 'beaker',
            'atom' => 'atom',
            'calculator' => 'calculator',
            'book' => 'book', 'document' => 'book',
            'globe' => 'globe', 'map' => 'globe',
            'chat' => 'chat', 'language' => 'chat', 'speech' => 'chat',
            'bulb' => 'bulb', 'light' => 'bulb',
            'chart' => 'chart', 'trending' => 'trending', 'arrow' => 'trending',
            'currency' => 'currency', 'banknote' => 'currency',
            'heart' => 'heart', 'medical' => 'heart',
            'music' => 'music', 'paint' => 'palette', 'palette' => 'palette', 'brush' => 'palette',
            'trophy' => 'trophy', 'sport' => 'trophy',
            'scale' => 'scale', 'law' => 'scale',
            'leaf' => 'leaf', 'environment' => 'leaf',
            'puzzle' => 'puzzle', 'aptitude' => 'puzzle',
            'newspaper' => 'newspaper', 'current' => 'newspaper',
            'wrench' => 'cog', 'cog' => 'cog', 'engineering' => 'cog',
            'brain' => 'brain', 'academic' => 'brain', 'psychology' => 'brain',
            'clipboard' => 'clipboard', 'exam' => 'clipboard',
        ];
        $lower = strtolower($stored);
        foreach ($map as $keyword => $key) {
            if (str_contains($lower, $keyword)) return $key;
        }
        return 'book';
    }
}
