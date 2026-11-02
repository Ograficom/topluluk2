@extends('layouts.app')

@section('title', 'Yazı oluştur')

@section('content')
    <header class="feed-heading">
        <h1 class="h3">Yazı oluştur</h1>
        <p class="color-gray">Hikâyeni toplulukla paylaş.</p>
    </header>

    @if ($errors->any())
        <div class="card card-body color-red" role="alert">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="addpost main__grid-feed">
        <form class="addpost__form card card-body" action="{{ route('posts.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <header class="addpost__header">
                <img class="addpost__header-avatar" src="{{ $user->avatarUrl() }}" alt="" width="40" height="40">
                <div class="addpost__author">
                    <span class="addpost__author-name">{{ $user->displayName() }}</span>
                    <span class="small color-gray">Yazı yayınlandıktan sonra profilinde görünür.</span>
                </div>
            </header>

            <div class="addpost__content"><div class="addpost__intro small color-gray">Yeni bir yayın oluştur</div>
                <ul class="addpost__fields">
                    <li>
                        <label class="addpost__label" for="post-title">Başlık</label>
                        <input class="addpost__title" id="post-title" name="title" type="text" value="{{ old('title') }}" maxlength="255" placeholder="Yazı başlığı" required>
                    </li>
                    <li>
                        <label class="addpost__label" for="post-excerpt">Kısa açıklama</label>
                        <textarea id="post-excerpt" name="excerpt" rows="2" maxlength="500" placeholder="Yazını kısaca tanıt (en fazla 500 karakter)" required>{{ old('excerpt') }}</textarea>
                    </li>
                    <li class="addpost__editor">
                        <label class="addpost__label" for="post-body">Yazı içeriği</label>
                        <textarea id="post-body" name="body" rows="14" maxlength="100000" placeholder="Hikâyeni burada anlat..." required>{{ old('body') }}</textarea>
                    </li>
                    <li class="addpost__actions">
                        <label class="addpost__label" for="post-cover">Kapak görseli</label>
                        <input class="form-attachments" id="post-cover" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp">
                    </li>
                    <li>
                        <label class="addpost__label" for="post-tags">Etiketler</label>
                        <input class="addpost__ultratag" id="post-tags" name="tags" type="text" value="{{ old('tags') }}" maxlength="500" placeholder="Etiketleri virgülle ayır (ör. gezi, teknoloji)">
                    </li>
                    <li>
                        <label class="addpost__label" for="post-channel">Kategori</label>
                        <select id="post-channel" name="channel_id">
                            <option value="">Kategori seçmeden yayınla</option>
                            @foreach ($channels as $channel)
                                <option value="{{ $channel->id }}" @selected((string) old('channel_id') === (string) $channel->id)>{{ $channel->name }}</option>
                            @endforeach
                        </select>
                    </li>
                </ul>

                <div class="addpost__settings">
                    <div class="checkbox">
                        <label class="form-check-label" for="post-adult">
                            <input class="form-check-input" id="post-adult" type="checkbox" name="is_adult" value="1" @checked(old('is_adult'))>
                            <span>18+ içerik olarak işaretle</span>
                        </label>
                    </div>
                </div>
                <footer class="addpost__footer">
                    <button class="btn btn-primary" type="submit">Yayınla</button>
                    <a class="btn btn-secondary-ghost" href="{{ route('posts.index') }}">Vazgeç</a>
                </footer>
            </div>
        </form>

        <aside class="addpost__sidebar">
            <section class="widget card card-body">
                <header><h2 class="widget__title h6">Yayınlamadan önce</h2></header>
                <div class="widget__content">
                    <ul>
                        <li>Açık ve açıklayıcı bir başlık seç.</li>
                        <li>İçeriğine uygun bir kategori belirle.</li>
                        <li>Yetişkinlere yönelik içeriği 18+ olarak işaretle.</li>
                    </ul>
                </div>
                @if (auth()->user()->is_admin)
                    <footer class="widget__footer">
                        <a class="link-accent" href="{{ route('channels.create') }}">Yeni kategori oluştur</a>
                    </footer>
                @endif
            </section>
            @include('layouts._footer')
        </aside>
    </div>
@endsection
