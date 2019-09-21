@component('mail::message')

Sehr geehrte(r) {{ $appointment->name }},

hiermit bestätigen wir Ihren Termin bei {{ $appointment->doctor->name }}.

@component('mail::table')
|         |                                                                                          |
|-----------------|----------------------------------------------------------------------------------|
| <strong>Datum</strong>   | {{  $appointment->start->format ('d.m.Y' ) }}                                            |
| <strong>Uhrzeit</strong> | {{  $appointment->start->format ('H:i' ) }}  - {{  $appointment->end->format ('H:i' ) }} |
| <strong>Arzt</strong>    | {{  $appointment->doctor->name  }}                                                       |
@endcomponent

<strong>Health For Life</strong><br>
Pulverturmgasse 22 | 1090 Wien<br>
<a href="https://goo.gl/maps/ov6VqZt169AY9qYY7">Auf Karte anzeigen</a>


Sollten Sie diesen Termin nicht wahrnehmen können, ersuchen wir Sie um telefonische Terminabsage (min. 48 Stunden davor) unter <a href="tel:+4319165916">+43&nbsp;(0)1&nbsp;916&nbsp;-&nbsp;5&nbsp;-&nbsp;916</a>, ansonten müssen wir Ihnen leider die Kosten über eine Ordination im Wert von 120€ in Rechnung stellen.


Mit freundlichen Grüßen<br>
Ihr HFL Team.
@endcomponent
