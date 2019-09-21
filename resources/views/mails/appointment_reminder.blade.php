@component('mail::message')

Sehr geehrte(r) {{ $appointment->name }},

Gerne möchten wir Sie an Ihren Termin bei <strong>{{ $appointment->doctor->name }}</strong> um <strong>{{ $appointment->start->format('d.m.Y') }}</strong> erinnern.

Ihr HFL Team.

<a href="https://www.healthforlife.at/kontakt/">Anfahrtsbeschreibung</a>

@endcomponent
