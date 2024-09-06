@php
    use App\Enums\StatusType;
@endphp

<x-app-layout>
    <x-slot:scripts>
        @vite(['resources/js/home.ts'])
    </x-slot:scripts>

    <x-slot:components>
        <x-toast></x-toast>
        <x-upload-modal modalId='uploadModal'></x-upload-modal>
    </x-slot:components>
    <div class="container">
        

        <div class="row mt-3">
            <h2 class="col">アップロード一覧</h2>
            <div class="col ms-auto text-end">
                <x-upload-modal-button modalId='uploadModal'></x-upload-modal-button>
            </div>
            
        </div>
        <div class="row">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">マップ</th>
                            <th scope="col">写真グループ名</th>
                            <th scope="col">ステータス</th>
                            <th scole="col">ファイル名</th>
                            <th scope="col">写真枚数</th>
                            <th scope="col">対象外件数</th>
                            <th scope="col">アップロード開始日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($photoGroups as $group)
                            <tr class="">
                                <td scope="row">
                                    @if ( $group->status_type === StatusType::SUCCESS->value)
                                        <button class="btn btn-sm btn-outline-dark">開く</button>
                                    @endif
                                </td>
                                <td scope="row">{{ $group->group_name }}</td>
                                <td><x-status-badge status="{{ $group->status }}"
                                        statusType="{{ $group->status_type }}"></x-status-badge></td>
                                <td>{{ $group->file_name }}</td>
                                <td>{{ $group->photo_count }}</td>
                                <td>{{ $group->non_photo_count }}</td>
                                <td>{{ $group->created_at }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

</x-app-layout>
