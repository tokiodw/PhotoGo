@props(['status', 'statusType'])

@php
    use App\Enums\StatusType;

    // statusTypeに応じたクラスを設定
    $badgeClass = match ($statusType) {
        StatusType::INACTIVE->value => 'bg-secondary',
        StatusType::ACTIVE->value => 'bg-primary',
        StatusType::WARNING->value => 'bg-warning',
        StatusType::ERROR->value => 'bg-danger',
        StatusType::SUCCESS->value => 'bg-success',
        default => 'bg-secondary', // デフォルトクラス
    };
@endphp

<span class="badge rounded-pill {{ $badgeClass }}">{{ $status }}</span>