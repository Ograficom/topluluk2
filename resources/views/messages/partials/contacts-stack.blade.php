<div class="space-y-4">
    @include('messages.partials.contact-group', [
        'title' => __('messages.contacts.following'),
        'contacts' => $followingContacts,
        'emptyText' => __('messages.contacts.following_empty'),
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>',
    ])

    @include('messages.partials.contact-group', [
        'title' => __('messages.contacts.followers'),
        'contacts' => $followerContacts,
        'emptyText' => __('messages.contacts.followers_empty'),
        'icon' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6"/><path d="M22 11h-6"/></svg>',
    ])
</div>
