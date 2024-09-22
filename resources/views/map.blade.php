<x-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/map.ts', 'resources/sass/map.scss'])
        {{-- <script async defer src="http://maps.google.com/maps/api/js?key=AIzaSyA9wwQnzK7VnbhdcBy6NzGTmug-w80pcLM&language=ja">
        </script> --}}
    </x-slot:scripts>

    <x-slot:components>
        <x-photo-modal></x-photo-modal>
        <x-photocluster-modal></x-photocluster-modal>
    </x-slot:components>

    <div class="d-flex flex-column justify-content-start h-100">
        <div class="m-3"><a href="{{ route('home.index') }}" class="btn btn-primary">一覧に戻る</a></div>
        <div class="flex-grow-1">
            <div id="map" class="h-100"></div>
        </div>
    </div>
</x-app-layout>
