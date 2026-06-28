<?php
return [
    'required'=>'Feltet :attribute er påkrævet.','email'=>'Feltet :attribute skal være en gyldig e-mailadresse.',
    'min'=>['string'=>'Feltet :attribute skal indeholde mindst :min tegn.','numeric'=>'Værdien :attribute skal være mindst :min.'],
    'max'=>['string'=>'Feltet :attribute må ikke indeholde mere end :max tegn.','numeric'=>'Værdien :attribute må ikke være større end :max.','file'=>'Filen :attribute må ikke være større end :max kilobytes.'],
    'confirmed'=>'Bekræftelsen af :attribute stemmer ikke overens.','unique'=>'Feltet :attribute er allerede taget.',
    'numeric'=>'Feltet :attribute skal være et tal.','integer'=>'Feltet :attribute skal være et heltal.',
    'in'=>'Den valgte :attribute er ugyldig.','url'=>'Feltet :attribute skal være en gyldig URL.',
    'image'=>'Feltet :attribute skal være et billede.','mimes'=>'Feltet :attribute skal være en fil af typen: :values.',
    'size'=>['file'=>'Filen :attribute skal være :size kilobytes.'],
    'between'=>['numeric'=>'Værdien :attribute skal være mellem :min og :max.'],
    'password'=>['min'=>'Adgangskoden skal indeholde mindst :min tegn.'],
    'attributes'=>['name'=>'navn','email'=>'e-mailadresse','password'=>'adgangskode','password_confirmation'=>'adgangskodebekræftelse','title'=>'titel','description'=>'beskrivelse','price'=>'pris','category_id'=>'kategori','pass_percentage'=>'bestået procent','duration_minutes'=>'varighed','max_attempts'=>'maksimale forsøg'],
    'login_failed'=>'Disse legitimationsoplysninger stemmer ikke overens med vores registreringer.',
    'account_inactive'=>'Din konto er blevet deaktiveret. Kontakt venligst support.',
    'throttle'=>'For mange loginforsøg. Prøv igen om :seconds sekunder.',
];
