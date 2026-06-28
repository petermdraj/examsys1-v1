<?php
return [
    'required'=>'Het veld :attribute is verplicht.','email'=>'Het veld :attribute moet een geldig e-mailadres zijn.',
    'min'=>['string'=>'Het veld :attribute moet minimaal :min tekens bevatten.','numeric'=>'De waarde :attribute moet minimaal :min zijn.'],
    'max'=>['string'=>'Het veld :attribute mag niet meer dan :max tekens bevatten.','numeric'=>'De waarde :attribute mag niet groter zijn dan :max.','file'=>'Het bestand :attribute mag niet groter zijn dan :max kilobytes.'],
    'confirmed'=>'De bevestiging van :attribute komt niet overeen.','unique'=>'Het veld :attribute is al in gebruik.',
    'numeric'=>'Het veld :attribute moet een getal zijn.','integer'=>'Het veld :attribute moet een geheel getal zijn.',
    'in'=>'De geselecteerde waarde voor :attribute is ongeldig.','url'=>'Het veld :attribute moet een geldige URL zijn.',
    'image'=>'Het veld :attribute moet een afbeelding zijn.','mimes'=>'Het veld :attribute moet een bestand zijn van het type: :values.',
    'size'=>['file'=>'Het bestand :attribute moet :size kilobytes zijn.'],
    'between'=>['numeric'=>'De waarde :attribute moet tussen :min en :max liggen.'],
    'password'=>['min'=>'Het wachtwoord moet minimaal :min tekens bevatten.'],
    'attributes'=>['name'=>'naam','email'=>'e-mailadres','password'=>'wachtwoord','password_confirmation'=>'wachtwoordbevestiging','title'=>'titel','description'=>'beschrijving','price'=>'prijs','category_id'=>'categorie','pass_percentage'=>'slagingspercentage','duration_minutes'=>'duur','max_attempts'=>'maximale pogingen'],
    'login_failed'=>'Deze gegevens komen niet overeen met onze records.',
    'account_inactive'=>'Uw account is gedeactiveerd. Neem contact op met de ondersteuning.',
    'throttle'=>'Te veel inlogpogingen. Probeer het over :seconds seconden opnieuw.',
];
