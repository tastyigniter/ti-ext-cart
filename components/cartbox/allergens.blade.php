@foreach ($menuItem->allergens->where('status', 1) ?? [] as $allergen)
    <a
        class="badge {{ !($hasMedia = $allergen->hasMedia('thumb')) ? 'badge-light' : '' }} rounded mt-2 mr-1"
        data-toggle="tooltip"
        title="{{ $allergen->name }}: {{ $allergen->description }}"
    >
        @if ($hasMedia)
            <img
                class="img-responsive img-rounded"
                alt="{{ $allergen->name }}"
                src="{{ $allergen->getThumb(['width' => $menuAllergenImageWidth, 'height' => $menuAllergenImageHeight]) }}"
            >
        @else
            {{ $allergen->name }}
        @endif
    </a>
@endforeach