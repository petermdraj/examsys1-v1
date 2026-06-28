<?php
return [
    'required'=>'Feltet :attribute er påkrevd.','email'=>'Feltet :attribute må være en gyldig e-postadresse.',
    'min'=>['string'=>'Feltet :attribute må inneholde minst :min tegn.','numeric'=>'Verdien :attribute må være minst :min.'],
    'max'=>['string'=>'Feltet :attribute kan ikke inneholde mer enn :max tegn.','numeric'=>'Verdien :attribute kan ikke være større enn :max.','file'=>'Filen :attribute kan ikke være større enn :max kilobytes.'],
    'confirmed'=>'Bekreftelsen av :attribute stemmer ikke overens.','unique'=>'Feltet :attribute er allerede tatt.',
    'numeric'=>'Feltet :attribute må være et tall.','integer'=>'Feltet :attribute må være et heltall.',
    'in'=>'Det valgte :attribute er ugyldig.','url'=>'Feltet :attribute må være en gyldig URL.',
    'image'=>'Feltet :attribute må være et bilde.','mimes'=>'Feltet :attribute må være en fil av typen: :values.',
    'size'=>['file'=>'Filen :attribute må være :size kilobytes.'],
    'between'=>['numeric'=>'Verdien :attribute må være mellom :min og :max.'],
    'password'=>['min'=>'Passordet må inneholde minst :min tegn.'],
    'attributes'=>['name'=>'navn','email'=>'e-postadresse','password'=>'passord','password_confirmation'=>'passordbekreftelse','title'=>'tittel','description'=>'beskrivelse','price'=>'pris','category_id'=>'kategori','pass_percentage'=>'beståttprosent','duration_minutes'=>'varighet','max_attempts'=>'maks forsøk'],
    'login_failed'=>'Disse legitimasjonsdataene samsvarer ikke med våre registreringer.',
    'account_inactive'=>'Kontoen din er deaktivert. Kontakt støtte.',
    'throttle'=>'For mange innloggingsforsøk. Prøv igjen om :seconds sekunder.',
];
