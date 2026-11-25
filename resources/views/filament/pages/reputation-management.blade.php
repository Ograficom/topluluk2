<x-filament-panels::page>
    <div class="mx-auto grid w-full max-w-5xl gap-6">
        <x-filament::section>
            <x-slot name="heading">Görev katılımcıları ve onay bekleyenler</x-slot>
            <x-slot name="description">Üyelerin gönderdiği görev tamamlama başvuruları. Sosyal medya hesabını kontrol edip onaylayın; yalnızca onaylanan üyeye ödül verilir.</x-slot>
            <div class="grid gap-4">
                <div>
                    <label for="participation-filter" class="text-sm font-medium">Başvuru durumu</label>
                    <select id="participation-filter" wire:model.live="participationFilter" class="rounded-lg border border-gray-300 px-3 py-2 text-sm dark:bg-gray-900">
                        <option value="pending">Onay bekleyenler</option>
                        <option value="approved">Onaylananlar</option>
                        <option value="rejected">Reddedilenler</option>
                        <option value="all">Tümü</option>
                    </select>
                </div>
                @php($participants = $this->getParticipants())
                <div class="overflow-x-auto">
                    <table class="w-full text-start text-sm">
                        <thead><tr class="border-b text-start">
                            <th class="p-3 text-start">Üye</th><th class="p-3 text-start">Görev</th><th class="p-3 text-start">Sosyal medya hesabı</th><th class="p-3 text-start">Başvuru tarihi</th><th class="p-3 text-start">Durum / işlem</th>
                        </tr></thead>
                        <tbody>
                        @forelse ($participants as $participant)
                            <tr wire:key="task-participant-{{ $participant->id }}" class="border-b">
                                <td class="p-3"><a class="text-primary-600 font-medium" href="{{ route('profiles.show', $participant->user->username ?: $participant->user_id) }}" target="_blank" rel="noopener">{{ '@'.$participant->user->publicUsername() }}</a></td>
                                <td class="p-3">{{ $participant->task_name }}</td>
                                <td class="p-3">{{ $participant->external_username ?: '—' }}</td>
                                <td class="p-3">{{ $participant->created_at?->format('d.m.Y H:i') }}</td>
                                <td class="p-3">
                                    @if ($participant->status === 'pending')
                                        <div class="flex flex-wrap gap-2">
                                            <x-filament::button size="sm" color="success" wire:click="reviewParticipation({{ $participant->id }}, true)" wire:confirm="Görevi onaylayıp ödülü vermek istiyor musunuz?">Onayla</x-filament::button>
                                            <x-filament::button size="sm" color="danger" wire:click="reviewParticipation({{ $participant->id }}, false)" wire:confirm="Başvuruyu reddetmek istiyor musunuz?">Reddet</x-filament::button>
                                        </div>
                                    @else
                                        {{ $participant->status === 'approved' ? 'Onaylandı' : 'Reddedildi' }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="p-4 text-gray-500">Bu durumda henüz katılımcı yok.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $participants->links() }}
            </div>
        </x-filament::section>
        <form wire:submit="save" class="grid gap-5">
            {{ $this->form }}
            <div class="flex justify-end">
                <x-filament::button type="submit">İtibar ayarlarını kaydet</x-filament::button>
            </div>
        </form>
    </div>
</x-filament-panels::page>
