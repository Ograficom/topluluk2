@if(isset($users) && $users->isNotEmpty())
    @foreach($users as $user)
        <div data-user-result data-user-id="{{ $user->id }}" class="flex items-center space-x-3 p-3 hover:bg-gray-50 rounded-lg cursor-pointer transition border border-gray-100">
            <img src="{{ $user->getAvatar() }}" alt="" class="w-10 h-10 rounded-full object-cover border border-gray-200" onerror="this.src='https://api.dicebear.com/7.x/avataaars/svg?seed=default'">
            <div>
                <p class="text-sm font-medium text-gray-900" data-user-name>{{ $user->name ?? $user->username }}</p>
                <p class="text-xs text-gray-400">@ {{ $user->username }}</p>
            </div>
        </div>
    @endforeach
@elseif(request()->has('search') && strlen(request()->search) >= 2)
    <p class="text-sm text-gray-400 p-3">{{ __('No users found.') }}</p>
@endif
