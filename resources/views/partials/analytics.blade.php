{{-- Umami: no cookies, no third party, production only. Do Not Track is respected. --}}
@if (app()->isProduction() && config('services.umami.script_url') && config('services.umami.website_id'))
<script defer src="{{ config('services.umami.script_url') }}" data-website-id="{{ config('services.umami.website_id') }}" data-do-not-track="true"></script>
@endif
