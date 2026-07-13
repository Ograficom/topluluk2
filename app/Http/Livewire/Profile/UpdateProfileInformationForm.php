<?php

namespace App\Http\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;
use Laravel\Jetstream\Http\Livewire\UpdateProfileInformationForm as BaseComponent;

class UpdateProfileInformationForm extends BaseComponent
{
    /**
     * Kullanici kapak fotografi.
     *
     * @var mixed
     */
    public $cover_photo;

    public function updateProfileInformation(UpdatesUserProfileInformation $updater)
    {
        $this->resetErrorBag();

        $payload = $this->state;

        if ($this->photo) {
            $payload['photo'] = $this->photo;
        }

        if ($this->cover_photo) {
            $payload['cover_photo'] = $this->cover_photo;
        }

        $updater->update(Auth::user(), $payload);

        if ($this->photo || $this->cover_photo) {
            return redirect()->route('profile.show');
        }

        $this->dispatch('saved');
        $this->dispatch('refresh-navigation-menu');
    }

    public function deleteCoverPhoto(): void
    {
        $user = Auth::user();

        if ($user->cover_photo_path) {
            Storage::disk('public')->delete($user->cover_photo_path);
        }

        $user->forceFill(['cover_photo_path' => null])->save();
    }
}
