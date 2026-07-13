<div class="flex gap-2 mt-0 sm:mt-4">
    <!-- PopOler Buton -->
    <a
        href="{{ route('blog.popular') }}"
        class="w-36 flex items-center justify-start gap-2 px-4 py-2 rounded-lg bg-transparent text-gray-700 hover:bg-white transition"
    >
        <!-- Star Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l2.036 6.29a1 1 0 00.95.69h6.614c.969 0 1.371 1.24.588 1.81l-5.35 3.885a1 1 0 00-.364 1.118l2.036 6.29c.3.921-.755 1.688-1.538 1.118l-5.35-3.885a1 1 0 00-1.176 0l-5.35 3.885c-.783.57-1.838-.197-1.538-1.118l2.036-6.29a1 1 0 00-.364-1.118L2.83 11.717c-.783-.57-.38-1.81.588-1.81h6.614a1 1 0 00.95-.69l2.036-6.29z"/>
        </svg>
        <span>PopOler</span>
    </a>

    <!-- Saat Butonu -->
    <a
        href="{{ route('blog.index') }}"
        class="w-36 flex items-center justify-start gap-2 px-4 py-2 rounded-lg bg-transparent text-gray-700 hover:bg-white transition"
    >
        <!-- Clock Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span>Saat</span>
    </a>
</div>
