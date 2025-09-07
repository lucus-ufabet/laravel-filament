<?php

namespace App\Filament\Resources\Websites\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Html;
use Illuminate\Support\Str;

class WebsiteForm
{
    public static function languageOptions(): array
    {
        return [
            'en' => 'English',
            'vi' => 'Vietnamese',
            'th' => 'Thai',
            'fil' => 'Filipino',
            'lo' => 'Lao',
            'id' => 'Indonesian',
            'ms' => 'Malay',
        ];
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Basics')->schema([
                    Grid::make(3)->schema([
                        TextInput::make('name')
                            ->label('Website Name')
                            ->live(onBlur: true)
                            ->required()
                            ->columnSpan(2)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if (! $get('slug')) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->unique(table: 'websites', column: 'slug', ignoreRecord: true)
                            ->required()
                            ->helperText('Used in endpoint /api/{slug}/{lang}/json'),
                    ]),

                Select::make('languages')
                    ->label('Languages')
                    ->multiple()
                    ->options(self::languageOptions())
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $langs = array_values(array_unique((array) ($state ?? [])));
                        $structure = (array) ($get('structure') ?? []);

                        $syncRows = function (?array $rows, array $langs, string $valueKey): array {
                            $rows = (array) ($rows ?? []);
                            $byLang = [];
                            foreach ($rows as $row) {
                                if (isset($row['lang'])) {
                                    $byLang[$row['lang']] = $row;
                                }
                            }
                            $new = [];
                            foreach ($langs as $l) {
                                $new[] = $byLang[$l] ?? ['lang' => $l, $valueKey => is_array($byLang) ? ($byLang[$l][$valueKey] ?? '') : ''];
                            }
                            return $new;
                        };

                        foreach ($structure as &$block) {
                            $type = strtolower($block['type'] ?? '');
                            $data = &$block['data'];
                            if (! is_array($data)) continue;

                            if (in_array($type, ['h1','h3','h4'], true)) {
                                $data['text'] = $syncRows($data['text'] ?? [], $langs, 'text');
                            }

                            if ($type === 'h2') {
                                $data['title'] = $syncRows($data['title'] ?? [], $langs, 'title');

                                foreach (($data['components'] ?? []) as &$component) {
                                    $ctype = strtolower($component['type'] ?? '');
                                    $cdata = &$component['data'];
                                    if (! is_array($cdata)) continue;
                                    if ($ctype === 'unfold') {
                                        foreach (($cdata['items'] ?? []) as &$it) {
                                            $it['title'] = $syncRows($it['title'] ?? [], $langs, 'title');
                                            $it['body'] = $syncRows($it['body'] ?? [], $langs, 'body');
                                        }
                                    } elseif (in_array($ctype, ['tabs','column_tabs'], true)) {
                                        foreach (($cdata['tabs'] ?? []) as &$tab) {
                                            $tab['label'] = $syncRows($tab['label'] ?? [], $langs, 'label');
                                        }
                                    } elseif ($ctype === 'row_tabs') {
                                        foreach (($cdata['rows'] ?? []) as &$row) {
                                            $row['label'] = $syncRows($row['label'] ?? [], $langs, 'label');
                                        }
                                    }
                                }
                                unset($component);
                            }

                            if ($type === 'tags') {
                                $data['tags'] = $syncRows($data['tags'] ?? [], $langs, 'values');
                            }

                            if ($type === 'unfold') {
                                foreach (($data['items'] ?? []) as &$it) {
                                    $it['title'] = $syncRows($it['title'] ?? [], $langs, 'title');
                                    $it['body'] = $syncRows($it['body'] ?? [], $langs, 'body');
                                }
                                unset($it);
                            }

                            if (in_array($type, ['tabs','column_tabs'], true)) {
                                foreach (($data['tabs'] ?? []) as &$tab) {
                                    $tab['label'] = $syncRows($tab['label'] ?? [], $langs, 'label');
                                }
                                unset($tab);
                            }

                            if ($type === 'row_tabs') {
                                foreach (($data['rows'] ?? []) as &$row) {
                                    $row['label'] = $syncRows($row['label'] ?? [], $langs, 'label');
                                }
                                unset($row);
                            }

                            if (in_array($type, ['content'], true)) {
                                foreach (($data['items'] ?? []) as &$itemBlock) {
                                    $itype = strtolower($itemBlock['type'] ?? '');
                                    $idata = &$itemBlock['data'];
                                    if ($itype === 'image') {
                                        $idata['alt'] = $syncRows($idata['alt'] ?? [], $langs, 'alt');
                                    } elseif (in_array($itype, ['h3','h4','paragraph'], true)) {
                                        $idata['text'] = $syncRows($idata['text'] ?? [], $langs, 'text');
                                    }
                                }
                                unset($itemBlock);
                            }
                        }
                        unset($block);

                        $set('structure', $structure);
                    })
                    ->helperText('Enable languages for this website'),

                    Toggle::make('is_published')
                        ->label('Published')
                        ->default(false),

                    Html::make(fn ($get) => $get('slug')
                        ? '<div class="text-sm text-gray-600">Preview Endpoint: <code>' . e(url('/api/' . $get('slug') . '/en/json')) . '</code></div>'
                        : '<div class="text-sm text-gray-600">Set a slug to see the preview API URL.</div>'
                    ),
                ])->collapsible(),

                Section::make('Sections & Components')->schema([
                    Builder::make('structure')
                        ->label('Structure')
                        ->blocks([
                            Builder\Block::make('h1')
                                ->label('H1 Section')
                                ->schema([
                                    Repeater::make('text')
                                        ->label('Text (per language)')
                                        ->schema([
                                            Select::make('lang')
                                                ->label('Lang')
                                                ->options(self::languageOptions())
                                                ->required(),
                                            Textarea::make('text')->label('Text')->rows(2)->required(),
                                        ])
                                        ->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))
                                        ->collapsed()
                                        ->columnSpanFull(),
                            ])
                            ->icon('heroicon-o-rectangle-stack'),

                        Builder\Block::make('h3')
                            ->label('H3 Section')
                            ->schema([
                                Repeater::make('text')
                                    ->label('Text (per language)')
                                    ->schema([
                                        Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                        Textarea::make('text')->label('Text')->rows(2)->required(),
                                    ])
                                    ->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))
                                    ->collapsed()
                                    ->columnSpanFull(),
                            ])
                            ->icon('heroicon-o-bars-3'),

                        Builder\Block::make('h4')
                            ->label('H4 Section')
                            ->schema([
                                Repeater::make('text')
                                    ->label('Text (per language)')
                                    ->schema([
                                        Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                        Textarea::make('text')->label('Text')->rows(2)->required(),
                                    ])
                                    ->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))
                                    ->collapsed()
                                    ->columnSpanFull(),
                            ])
                            ->icon('heroicon-o-bars-3'),

                        Builder\Block::make('h2')
                            ->label('H2 Section')
                            ->schema([
                                Repeater::make('title')
                                    ->label('Title (per language)')
                                    ->schema([
                                        Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                        TextInput::make('title')->label('Title')->required(),
                                    ])
                                    ->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'title' => ''], (array) ($get('languages') ?? [])))
                                    ->collapsed(),

                                Builder::make('components')
                                    ->label('Right-side Components')
                                    ->blocks([
                                        Builder\Block::make('unfold')
                                            ->label('Unfold')
                                            ->schema([
                                                Repeater::make('items')
                                                    ->label('Items')
                                                    ->schema([
                                                        Repeater::make('title')->label('Title (per lang)')->schema([
                                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                            TextInput::make('title')->label('Title')->required(),
                                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'title' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                                        Repeater::make('body')->label('Body (per lang)')->schema([
                                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                            Textarea::make('body')->label('Body')->rows(3)->required(),
                                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'body' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                                    ])
                                                    ->collapsed(),
                                            ])->icon('heroicon-o-chevron-down'),

                                        Builder\Block::make('tabs')
                                            ->label('Tabs')
                                            ->schema([
                                                Repeater::make('tabs')
                                                    ->schema([
                                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                            TextInput::make('label')->label('Label')->required(),
                                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                                        Repeater::make('rows')
                                                            ->label('Rows')
                                                            ->schema([
                                                                Textarea::make('content')->label('Content')->rows(2),
                                                            ])->collapsed(),
                                                    ])->collapsed(),
                                            ])->icon('heroicon-o-folder'),

                                        Builder\Block::make('column_tabs')
                                            ->label('Column Tabs')
                                            ->schema([
                                                TextInput::make('columns')->numeric()->minValue(1)->default(2),
                                                Repeater::make('tabs')
                                                    ->schema([
                                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                            TextInput::make('label')->label('Label')->required(),
                                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                                        Repeater::make('rows')
                                                            ->schema([
                                                                Textarea::make('content')->rows(2),
                                                            ])->collapsed(),
                                                    ])->collapsed(),
                                            ])->icon('heroicon-o-view-columns'),

                                        Builder\Block::make('row_tabs')
                                            ->label('Row Tabs')
                                            ->schema([
                                                Repeater::make('rows')
                                                    ->schema([
                                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                            TextInput::make('label')->label('Label')->required(),
                                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                                        Repeater::make('items')
                                                            ->schema([
                                                                TextInput::make('text')->label('Item text'),
                                                            ])->collapsed(),
                                                    ])->collapsed(),
                                            ])->icon('heroicon-o-rectangle-group'),
                                    ]),

                                Repeater::make('middleware')
                                    ->label('Middleware sources')
                                    ->schema([
                                        Select::make('type')
                                            ->options([
                                                'LiveScore' => 'Live Score',
                                                'BetBoost' => 'BetBoost',
                                                'GamesSlot' => 'Games Slot',
                                            ])->required(),
                                    ])->collapsed(),
                            ])
                            ->icon('heroicon-o-document-text'),

                        Builder\Block::make('unfold')
                            ->label('Unfold Section')
                            ->schema([
                                Repeater::make('items')
                                    ->label('Items')
                                    ->schema([
                                        Repeater::make('title')->label('Title (per lang)')->schema([
                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                            TextInput::make('title')->label('Title')->required(),
                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'title' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        Repeater::make('body')->label('Body (per lang)')->schema([
                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                            Textarea::make('body')->label('Body')->rows(3)->required(),
                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'body' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                    ])->collapsed(),
                            ])->icon('heroicon-o-chevron-down'),

                        Builder\Block::make('tabs')
                            ->label('Tabs Section')
                            ->schema([
                                Repeater::make('tabs')
                                    ->schema([
                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                            TextInput::make('label')->label('Label')->required(),
                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        Repeater::make('rows')
                                            ->label('Rows')
                                            ->schema([
                                                Textarea::make('content')->label('Content')->rows(2),
                                            ])->collapsed(),
                                    ])->collapsed(),
                            ])->icon('heroicon-o-folder'),

                        Builder\Block::make('column_tabs')
                            ->label('Column Tabs Section')
                            ->schema([
                                TextInput::make('columns')->numeric()->minValue(1)->default(2),
                                Repeater::make('tabs')
                                    ->schema([
                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                            TextInput::make('label')->label('Label')->required(),
                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        Repeater::make('rows')
                                            ->schema([
                                                Textarea::make('content')->rows(2),
                                            ])->collapsed(),
                                    ])->collapsed(),
                            ])->icon('heroicon-o-view-columns'),

                        Builder\Block::make('row_tabs')
                            ->label('Row Tabs Section')
                            ->schema([
                                Repeater::make('rows')
                                    ->schema([
                                        Repeater::make('label')->label('Label (per lang)')->schema([
                                            Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                            TextInput::make('label')->label('Label')->required(),
                                        ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'label' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        Repeater::make('items')
                                            ->schema([
                                                TextInput::make('text')->label('Item text'),
                                            ])->collapsed(),
                                    ])->collapsed(),
                            ])->icon('heroicon-o-rectangle-group'),

                        Builder\Block::make('tags')
                            ->label('Tags Section')
                            ->schema([
                                Repeater::make('tags')
                                    ->label('Tags per language')
                                    ->schema([
                                        Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                        TagsInput::make('values')->label('Tags')->placeholder('Add tag...')->required(),
                                    ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'values' => []], (array) ($get('languages') ?? [])))->collapsed(),

                                Repeater::make('middleware')
                                    ->label('Middleware sources')
                                    ->schema([
                                        Select::make('type')
                                            ->options([
                                                'LiveScore' => 'Live Score',
                                                'BetBoost' => 'BetBoost',
                                                'GamesSlot' => 'Games Slot',
                                            ])->required(),
                                    ])->collapsed(),
                            ])
                            ->icon('heroicon-o-hashtag'),

                        Builder\Block::make('content')
                            ->label('Add Content')
                            ->schema([
                                Builder::make('items')
                                    ->blocks([
                                        Builder\Block::make('h3')->label('H3')->schema([
                                            Repeater::make('text')->label('Text (per lang)')->schema([
                                                Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                Textarea::make('text')->rows(2)->required(),
                                            ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        ])->icon('heroicon-o-bars-3'),
                                        Builder\Block::make('h4')->label('H4')->schema([
                                            Repeater::make('text')->label('Text (per lang)')->schema([
                                                Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                Textarea::make('text')->rows(2)->required(),
                                            ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        ])->icon('heroicon-o-bars-3'),
                                        Builder\Block::make('paragraph')->label('Paragraph')->schema([
                                            Repeater::make('text')->label('Text (per lang)')->schema([
                                                Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                Textarea::make('text')->rows(3)->required(),
                                            ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'text' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        ])->icon('heroicon-o-bars-3'),
                                        Builder\Block::make('image')->label('Image')->schema([
                                            TextInput::make('url')->label('Image URL')->url()->required(),
                                            Repeater::make('alt')->label('Alt (per lang)')->schema([
                                                Select::make('lang')->label('Lang')->options(self::languageOptions())->required(),
                                                TextInput::make('alt')->label('Alt text')->required(),
                                            ])->default(fn ($get) => array_map(fn ($l) => ['lang' => $l, 'alt' => ''], (array) ($get('languages') ?? [])))->collapsed(),
                                        ])->icon('heroicon-o-photo'),
                                    ])
                            ])
                            ->icon('heroicon-o-plus-circle'),
                        ])
                        ->collapsible()
                        ->columnSpanFull(),
                ])->collapsible(),
            ]);
    }
}
