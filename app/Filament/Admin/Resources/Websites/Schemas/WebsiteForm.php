<?php

namespace App\Filament\Admin\Resources\Websites\Schemas;

use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\KeyValue;
use Filament\Schemas\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;

class WebsiteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                FormSection::make('Site Identity')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('name')->required()->maxLength(255),
                            TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true),
                        ]),
                        Grid::make(3)->schema([
                            TextInput::make('default_language')->label('Default language')->default('en')->maxLength(8),
                            TagsInput::make('supported_languages')->placeholder('en, vi, fr')->suggestions(['en','vi','fr','de','es']),
                            Toggle::make('is_active')->default(true),
                        ]),
                        Grid::make(2)->schema([
                            FileUpload::make('logo_path')->image()->imageEditor()->directory('logos')->disk('public')->label('Logo'),
                            FileUpload::make('favicon_path')->image()->directory('favicons')->disk('public')->label('Favicon'),
                        ]),
                    ])->columns(1),

                FormSection::make('SEO & Analytics')
                    ->schema([
                        TextInput::make('google_tag')->placeholder('G-XXXXXXXXXX')->helperText('Google Tag / GA4 Measurement ID'),
                        Textarea::make('robots_txt')->rows(6)->helperText('robots.txt content'),
                    ])->columns(1),

                FormSection::make('Site Manifest')
                    ->schema([
                        KeyValue::make('site_manifest')->reorderable()->keyLabel('Key')->valueLabel('Value')->addButtonLabel('Add')->helperText('Basic manifest key/value. You can also store full JSON if needed.'),
                    ])->columns(1),
            ]);
    }
}
