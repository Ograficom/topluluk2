<?php

namespace App\Livewire\Admin\Settings;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Livewire\Component;

class Points extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        $settings = [
            'rating_active' => settings()->group('advanced')->get('rating_active'),
            'registration' => config('points.registration'),
            'login' => config('points.login'),
            'created_published_story' => config('points.created_published_story'),
            'created_comment' => config('points.created_comment'),
            'created_comment_reply' => config('points.created_comment_reply'),
            'deleted_published_story' => config('points.deleted_published_story'),
            'deleted_comment' => config('points.deleted_comment'),
            'deleted_comment_reply' => config('points.deleted_comment_reply'),
            'liked_post' => config('points.liked_post'),
            'liked_comment' => config('points.liked_comment'),
            'unliked_post' => config('points.unliked_post'),
            'unliked_comment' => config('points.unliked_comment'),
        ];

        $this->form->fill($settings);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('Users earn points for various activities'))
                    ->description(__('The Point System is a user rating system that rewards users for their contributions and activities on this platform. The system is designed to encourage high-quality content, engagement, and community participation.'))
                    ->icon('heroicon-o-star')
                    ->schema([
                        Toggle::make('rating_active')
                            ->label(__('Enable points system'))
                            ->onIcon('heroicon-m-bolt')
                            ->offIcon('heroicon-m-bolt-slash')
                            ->live(onBlur: true),
                        Grid::make([
                            'default' => 1,
                            'md' => 2,
                        ])
                            ->schema([
                                TextInput::make('registration')
                                    ->label(__('Registration')),
                                TextInput::make('login')
                                    ->label(__('Authentication')),
                                TextInput::make('created_published_story')
                                    ->label(__('New published story')),
                                TextInput::make('deleted_published_story')
                                    ->label(__('Deleted published story')),
                                TextInput::make('created_comment')
                                    ->label(__('New comment')),
                                TextInput::make('deleted_comment')
                                    ->label(__('Deleted comment')),
                                TextInput::make('created_comment_reply')
                                    ->label(__('New comment reply')),
                                TextInput::make('deleted_comment_reply')
                                    ->label(__('Deleted comment reply')),
                                TextInput::make('liked_post')
                                    ->label(__('Like story')),
                                TextInput::make('unliked_post')
                                    ->label(__('Unlike story')),
                                TextInput::make('liked_comment')
                                    ->label(__('Like comment')),
                                TextInput::make('unliked_comment')
                                    ->label(__('Unlike comment')),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function store()
    {
        if (env('DEMO_MODE')) {
            Notification::make()
                ->warning()
                ->title('Opps! You are in demo mode')
                ->seconds(10)
                ->send();

            return;
        }

        if ($this->form->getState()['rating_active']) {
            settings()->group('advanced')->set('rating_active', $this->form->getState()['rating_active']);

            Artisan::call('config:clear');
        } else {
            settings()->group('advanced')->set('rating_active', $this->form->getState()['rating_active']);
        }

        if ($this->form->getState()['registration']) {
            Config::write('points.registration', $this->form->getState()['registration']);
        }

        if ($this->form->getState()['login']) {
            Config::write('points.login', $this->form->getState()['login']);
        }

        if ($this->form->getState()['created_published_story']) {
            Config::write('points.created_published_story', $this->form->getState()['created_published_story']);
        }

        if ($this->form->getState()['deleted_published_story']) {
            Config::write('points.deleted_published_story', $this->form->getState()['deleted_published_story']);
        }

        if ($this->form->getState()['created_comment']) {
            Config::write('points.created_comment', $this->form->getState()['created_comment']);
        }

        if ($this->form->getState()['deleted_comment']) {
            Config::write('points.deleted_comment', $this->form->getState()['deleted_comment']);
        }

        if ($this->form->getState()['created_comment_reply']) {
            Config::write('points.created_comment_reply', $this->form->getState()['created_comment_reply']);
        }

        if ($this->form->getState()['deleted_comment_reply']) {
            Config::write('points.deleted_comment_reply', $this->form->getState()['deleted_comment_reply']);
        }

        if ($this->form->getState()['liked_post']) {
            Config::write('points.liked_post', $this->form->getState()['liked_post']);
        }

        if ($this->form->getState()['unliked_post']) {
            Config::write('points.unliked_post', $this->form->getState()['unliked_post']);
        }

        if ($this->form->getState()['liked_comment']) {
            Config::write('points.liked_comment', $this->form->getState()['liked_comment']);
        }

        if ($this->form->getState()['unliked_comment']) {
            Config::write('points.unliked_comment', $this->form->getState()['unliked_comment']);
        }

        // Clear all cache
        Artisan::call('optimize:clear');

        Notification::make()
            ->success()
            ->title(__('Settings successfully updated'))
            ->seconds(10)
            ->send();
    }

    public function render()
    {
        return view('livewire.admin.settings.appearance');
    }
}
