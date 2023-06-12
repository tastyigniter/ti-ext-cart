@foreach ($menuItem->allergens->where('status', 1) ?? [] as $allergen)
    <span
        @class([
            'badge bg-light text-dark fw-normal rounded-pill shadow-sm px-2',
            'py-2' => !$allergen->hasMedia('thumb'),
            'me-2' => !$loop->last
        ])
        data-bs-toggle="tooltip"
        title="{{ $allergen->name }}: {{ $allergen->description }}"
    >
        @if ($allergen->hasMedia('thumb'))
            <img
                class="img-fluid rounded-pill"
                alt="{{ $allergen->name }}"
                src="{{ $allergen->getThumb(['width' => $menuAllergenImageWidth, 'height' => $menuAllergenImageHeight]) }}"
            />
        @endif
        {{ $allergen->name }}
    </span>
@endforeach
