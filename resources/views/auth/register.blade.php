<x-guest-layout>
    <div class="card shadow-lg border-0 rounded-lg">
        <div class="card-header">
            <h3 class="text-start font-weight-light my-4 ms-2">PhotoGo アカウントを作成</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('register') }}">
                @csrf
                <div class="form-floating mb-3 mx-3">
                    <input class="form-control" id="inputName" type="text" name="name"
                        required autofocus autocomplete="name" />
                    <label for="inputName">名前</label>
                </div>
                <div class="form-floating mb-3 mx-3">
                    <input class="form-control" id="inputEmail" type="email" name="email"
                        placeholder="name@example.com" required　 autocomplete="username"/>
                    <label for="inputEmail">メールアドレス</label>
                </div>
                <div class="form-floating mb-3 mx-3">
                    <input class="form-control" id="inputPassword" type="password" name="password"
                        placeholder="Password" required autocomplete="new-password"/>
                    <label for="inputPassword">パスワード</label>
                </div>
                <div class="form-floating mb-3 mx-3">
                    <input class="form-control" id="inputConfirmPassword" type="password" name="password_confirmation"
                        placeholder="Password" required autocomplete="new-password"/>
                    <label for="inputConfirmPassword">パスワード(確認用)</label>
                </div>
                <div class="d-flex align-items-center justify-content-end mt-4 mb-0 me-3">
                    <button class="btn btn-primary" type="submit">作成</a>
                </div>
            </form>
        </div>
        <div class="card-footer text-center py-3">
            <div>既にアカウントをお持ちですか？ <a href="{{ route('login') }}">ログイン</a></div>
        </div>
    </div>
    {{-- <form method="POST" action="{{ route('register') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form> --}}
</x-guest-layout>
