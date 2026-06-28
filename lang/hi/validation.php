<?php

return [
    'required'        => ':attribute फ़ील्ड आवश्यक है।',
    'email'           => ':attribute एक वैध ईमेल पता होना चाहिए।',
    'min'             => [
        'string'  => ':attribute कम से कम :min अक्षर होना चाहिए।',
        'numeric' => ':attribute कम से कम :min होना चाहिए।',
    ],
    'max'             => [
        'string'  => ':attribute :max से अधिक अक्षर नहीं होने चाहिए।',
        'numeric' => ':attribute :max से अधिक नहीं होना चाहिए।',
        'file'    => ':attribute :max किलोबाइट से अधिक नहीं होनी चाहिए।',
    ],
    'confirmed'       => ':attribute की पुष्टि मेल नहीं खाती।',
    'unique'          => ':attribute पहले से लिया जा चुका है।',
    'numeric'         => ':attribute एक संख्या होनी चाहिए।',
    'integer'         => ':attribute एक पूर्णांक होना चाहिए।',
    'in'              => 'चयनित :attribute अमान्य है।',
    'url'             => ':attribute एक वैध URL होना चाहिए।',
    'image'           => ':attribute एक छवि होनी चाहिए।',
    'mimes'           => ':attribute इस प्रकार की फ़ाइल होनी चाहिए: :values।',
    'size'            => [
        'file' => ':attribute :size किलोबाइट होनी चाहिए।',
    ],
    'between'         => [
        'numeric' => ':attribute :min और :max के बीच होनी चाहिए।',
    ],
    'password'        => [
        'min' => 'पासवर्ड कम से कम :min अक्षर का होना चाहिए।',
    ],

    'attributes' => [
        'name'             => 'नाम',
        'email'            => 'ईमेल पता',
        'password'         => 'पासवर्ड',
        'password_confirmation' => 'पासवर्ड पुष्टि',
        'title'            => 'शीर्षक',
        'description'      => 'विवरण',
        'price'            => 'मूल्य',
        'category_id'      => 'श्रेणी',
        'pass_percentage'  => 'उत्तीर्ण प्रतिशत',
        'duration_minutes' => 'अवधि',
        'max_attempts'     => 'अधिकतम प्रयास',
    ],

    'login_failed'    => 'ये क्रेडेंशियल हमारे रिकॉर्ड से मेल नहीं खाते।',
    'account_inactive' => 'आपका खाता निष्क्रिय कर दिया गया है। कृपया सहायता से संपर्क करें।',
    'throttle'        => 'बहुत अधिक लॉग इन प्रयास। कृपया :seconds सेकंड बाद पुनः प्रयास करें।',
];
