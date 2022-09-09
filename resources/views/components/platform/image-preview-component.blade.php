@if($media->hasGeneratedConversion('platform'))
    <img class="img-fluid rounded" src="{{ $media->getUrl('platform') }}" alt="{{ $media->name }}">
@else
    <img class="img-fluid rounded" src="{{ url('images/no_image.svg') }}" alt="{{ $media->name }}">
@endif
