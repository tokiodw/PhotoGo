<x-guest-layout>
    <div class="card shadow-lg border-0 rounded-lg">
        <div class="card-header">
            <h3 class="text-start font-weight-light my-4 ms-2">PhotoGo へログイン</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-floating mb-3">
                    <input class="form-control" id="inputEmail" type="email" name="email"
                        placeholder="name@example.com" />
                    <label for="inputEmail">メールアドレス</label>
                </div>
                <div class="form-floating mb-3">
                    <input class="form-control" id="inputPassword" type="password" name="password"
                        placeholder="Password" />
                    <label for="inputPassword">パスワード</label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input" id="inputRememberPassword" type="checkbox"
                        name="remember" />
                    <label class="form-check-label" for="inputRememberPassword">パスワードを記憶する</label>
                </div>
                <div class="d-flex align-items-center justify-content-end mt-4 mb-0">
                    <button class="btn btn-primary" type="submit">ログイン</a>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3">
            <div><a href="{{ route('register') }}">新規登録</a></div>
        </div>
    </div>
</x-guest-layout>