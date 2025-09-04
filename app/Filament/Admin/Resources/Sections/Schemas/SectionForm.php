<?php

namespace App\Filament\Admin\Resources\Sections\Schemas;

use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Forms\Components\Repeater;

class SectionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Section')
                    ->schema([
                        Grid::make(3)->schema([
                            Select::make('website_id')->relationship('website', 'name')->required(),
                            TextInput::make('name')->required()->maxLength(255),
                            TextInput::make('slug')->required()->alphaDash()->maxLength(255),
                        ]),
                        Grid::make(4)->schema([
                            TextInput::make('language')->default('en')->maxLength(8),
                            Select::make('type')->options([
                                'h1' => 'H1 Section',
                                'h2' => 'H2 Section',
                                'tags' => 'Tags Section',
                                'content' => 'Add Content',
                            ])->required(),
                            TextInput::make('order')->numeric()->default(0),
                            Toggle::make('is_published')->default(true),
                        ]),
                    ])->columns(1),

                FormSection::make('Tags')
                    ->schema([
                        TagsInput::make('tags'),
                    ])->visible(fn (Get $get) => $get('type') === 'tags'),

                FormSection::make('Components')
                    ->schema([
                        Builder::make('components')
                            ->blocks([
                                Block::make('heading')
                                    ->schema([
                                        Select::make('level')->options([
                                            'h1' => 'H1',
                                            'h2' => 'H2',
                                            'h3' => 'H3',
                                            'h4' => 'H4',
                                        ])->default('h2'),
                                        TextInput::make('text')->label('Heading text')->required(),
                                    ])->columns(2),

                                Block::make('paragraph')
                                    ->schema([
                                        Textarea::make('text')->rows(5)->label('Paragraph')->required(),
                                    ]),

                                Block::make('image')
                                    ->schema([
                                        FileUpload::make('path')->image()->directory('images')->disk('public')->label('Image'),
                                        TextInput::make('alt')->label('Alt text'),
                                    ])->columns(2),

                                Block::make('unfold')
                                    ->schema([
                                        Repeater::make('items')
                                            ->schema([
                                                TextInput::make('title')->required(),
                                                Textarea::make('content')->rows(4)->required(),
                                                Toggle::make('open')->label('Open by default')->default(false),
                                            ])->collapsed(false)->createItemButtonLabel('Add item'),
                                    ])->columns(1),

                                Block::make('tabs')
                                    ->schema([
                                        Select::make('orientation')
                                            ->options([
                                                'row' => 'Row Tabs',
                                                'column' => 'Column Tabs',
                                            ])->default('row'),
                                        Repeater::make('tabs')
                                            ->schema([
                                                TextInput::make('label')->required(),
                                                Builder::make('content')
                                                    ->blocks([
                                                        Block::make('heading')->schema([
                                                            Select::make('level')->options([
                                                                'h3' => 'H3',
                                                                'h4' => 'H4',
                                                            ])->default('h3'),
                                                            TextInput::make('text')->required(),
                                                        ])->columns(2),
                                                        Block::make('paragraph')->schema([
                                                            Textarea::make('text')->rows(5)->required(),
                                                        ]),
                                                        Block::make('image')->schema([
                                                            FileUpload::make('path')->image()->directory('images')->disk('public'),
                                                            TextInput::make('alt'),
                                                        ])->columns(2),
                                                    ]),
                                            ])->collapsed(false)->createItemButtonLabel('Add Tab'),
                                    ])->columns(1),
                            ])
                            ->hidden(fn (Get $get) => $get('type') === 'tags'),
                    ]),

                FormSection::make('Audience & Middleware')
                    ->schema([
                        Toggle::make('user_selectable')->label('User can select')->default(false),
                        TagsInput::make('audiences')->placeholder('e.g. guest, member, vip'),
                        TagsInput::make('middlewares')->placeholder('e.g. live-score, bet-boost'),
                    ])->columns(1),
            ]);
    }
}
