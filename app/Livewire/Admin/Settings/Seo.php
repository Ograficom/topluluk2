<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Seo extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $seoSettings = settings()->group('seo')->all(false);

        $settings = [
            'meta_title' => $seoSettings['meta_title'],
            'meta_description' => $seoSettings['meta_description'],
            'meta_keywords' => $seoSettings['meta_keywords'],
            'og_site_name' => $seoSettings['og_site_name'],
            'og_title' => $seoSettings['og_title'],
            'og_description' => $seoSettings['og_description'],
            'og_url' => $seoSettings['og_url'],
            'og_type' => $seoSettings['og_type'],
            'og_image' => $seoSettings['og_image'],
        ];
        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Search Engine Optimization'))
                    ->description(__('Search engine optimization is the process of improving the quality and quantity of website traffic to a website or a web page from search engines.'))
                    ->icon('heroicon-o-magnifying-glass-circle')
                    ->schema([
                        TextInput::make('meta_title')
                            ->label(__('Site meta title'))
                            ->placeholder(__('Site name'))
                            ->required()
                            ->minLength(5)
                            ->maxLength(60),
                        Textarea::make('meta_description')
                            ->label(__('Site meta description'))
                            ->placeholder(__('Site meta description'))
                            ->autosize()
                            ->rows(3)
                            ->maxLength(160),
                        TextInput::make('meta_keywords')
                            ->label(__('Site meta keywords'))
                            ->placeholder(__('Site meta keywords'))
                            ->maxLength(160),
                    ]),
                Section::make(__('Open Graph Meta Tags'))
                    ->description(__('The Open Graph protocol is a markup that determines the type of link to a site in social networks and instant messengers. Thanks to this micro-markup, the correct image and the specified text with a brief description are added to the post, which makes the post look more attractive, it becomes like an ad in contextual advertising and gets more attention. Such a kind of banner significantly increases the click-through rate and the number of reposts.'))
                    ->schema([
                        TextInput::make('og_site_name')
                            ->label(__('OG site name'))
                            ->placeholder(__('OG site name'))
                            ->maxLength(60),
                        TextInput::make('og_title')
                            ->label(__('OG title'))
                            ->placeholder(__('OG title'))
                            ->maxLength(60),
                        Textarea::make('og_description')
                            ->label(__('OG description'))
                            ->placeholder(__('OG description'))
                            ->autosize()
                            ->rows(3)
                            ->maxLength(160),
                        TextInput::make('og_url')
                            ->label(__('OG url'))
                            ->placeholder(__('OG url'))
                            ->url()
                            ->maxLength(60),
                        TextInput::make('og_type')
                            ->label(__('OG type'))
                            ->placeholder(__('OG type'))
                            ->maxLength(100),
                        FileUpload::make('og_image')
                            ->label(__('OG image'))
                            ->image()
                            ->acceptedFileTypes(['image/jpg', 'image/jpeg', 'image/png'])
                            ->disk(getCurrentDisk())
                            ->directory('media')
                            ->visibility('public')
                            ->maxSize(1024)
                            ->afterStateUpdated(fn () => $this->validateOnly('data.og_image')),
                    ]),
            ])
            ->statePath('data');
    }

    public function store(): void
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        if (! is_null($this->form->getState()['meta_title'])) {
            settings()->group('seo')->set('meta_title', $this->form->getState()['meta_title']);
        } else {
            settings()->group('seo')->set('site_name', '');
        }

        if (! is_null($this->form->getState()['meta_description'])) {
            settings()->group('seo')->set('meta_description', $this->form->getState()['meta_description']);
        } else {
            settings()->group('seo')->set('meta_description', '');
        }

        if (! is_null($this->form->getState()['meta_keywords'])) {
            settings()->group('seo')->set('meta_keywords', $this->form->getState()['meta_keywords']);
        } else {
            settings()->group('seo')->set('meta_keywords', '');
        }

        if (! is_null($this->form->getState()['og_site_name'])) {
            settings()->group('seo')->set('og_site_name', $this->form->getState()['og_site_name']);
        } else {
            settings()->group('seo')->set('og_site_name', '');
        }

        if (! is_null($this->form->getState()['og_title'])) {
            settings()->group('seo')->set('og_title', $this->form->getState()['og_title']);
        } else {
            settings()->group('seo')->set('og_title', '');
        }

        if (! is_null($this->form->getState()['og_description'])) {
            settings()->group('seo')->set('og_description', $this->form->getState()['og_description']);
        } else {
            settings()->group('seo')->set('og_description', '');
        }

        if (! is_null($this->form->getState()['og_url'])) {
            settings()->group('seo')->set('og_url', $this->form->getState()['og_url']);
        } else {
            settings()->group('seo')->set('og_url', '');
        }

        if (! is_null($this->form->getState()['og_type'])) {
            settings()->group('seo')->set('og_type', $this->form->getState()['og_type']);
        } else {
            settings()->group('seo')->set('og_type', '');
        }

        if (! is_null($this->form->getState()['og_image'])) {
            settings()->group('seo')->set('og_image', $this->form->getState()['og_image']);
        } else {
            $og_image = settings()->group('seo')->get('og_image');
            if (isset($og_image) && Storage::disk(getCurrentDisk())->exists($og_image)) {
                Storage::disk(getCurrentDisk())->delete($og_image);

                settings()->group('seo')->set('og_image', '');
            }
        }

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.seo');
    }
}
