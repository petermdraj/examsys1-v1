<?php
return [
    'required'=>'Fältet :attribute är obligatoriskt.','email'=>'Fältet :attribute måste vara en giltig e-postadress.',
    'min'=>['string'=>'Fältet :attribute måste innehålla minst :min tecken.','numeric'=>'Värdet :attribute måste vara minst :min.'],
    'max'=>['string'=>'Fältet :attribute får inte innehålla mer än :max tecken.','numeric'=>'Värdet :attribute får inte vara större än :max.','file'=>'Filen :attribute får inte vara större än :max kilobyte.'],
    'confirmed'=>'Bekräftelsen av :attribute stämmer inte överens.','unique'=>'Fältet :attribute är redan taget.',
    'numeric'=>'Fältet :attribute måste vara ett tal.','integer'=>'Fältet :attribute måste vara ett heltal.',
    'in'=>'Det valda :attribute är ogiltigt.','url'=>'Fältet :attribute måste vara en giltig URL.',
    'image'=>'Fältet :attribute måste vara en bild.','mimes'=>'Fältet :attribute måste vara en fil av typen: :values.',
    'size'=>['file'=>'Filen :attribute måste vara :size kilobyte.'],
    'between'=>['numeric'=>'Värdet :attribute måste vara mellan :min och :max.'],
    'password'=>['min'=>'Lösenordet måste innehålla minst :min tecken.'],
    'attributes'=>['name'=>'namn','email'=>'e-postadress','password'=>'lösenord','password_confirmation'=>'lösenordsbekräftelse','title'=>'titel','description'=>'beskrivning','price'=>'pris','category_id'=>'kategori','pass_percentage'=>'godkäntprocent','duration_minutes'=>'varaktighet','max_attempts'=>'max försök'],
    'login_failed'=>'Dessa uppgifter stämmer inte överens med våra register.',
    'account_inactive'=>'Ditt konto har inaktiverats. Kontakta supporten.',
    'throttle'=>'För många inloggningsförsök. Försök igen om :seconds sekunder.',
];
