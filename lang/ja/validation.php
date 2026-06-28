<?php
return [
    'required'=>':attributeフィールドは必須です。','email'=>':attributeは有効なメールアドレスでなければなりません。',
    'min'=>['string'=>':attributeは:min文字以上でなければなりません。','numeric'=>':attributeは:min以上でなければなりません。'],
    'max'=>['string'=>':attributeは:max文字以下でなければなりません。','numeric'=>':attributeは:max以下でなければなりません。','file'=>':attributeは:maxキロバイト以下でなければなりません。'],
    'confirmed'=>':attributeの確認が一致しません。','unique'=>':attributeはすでに使用されています。',
    'numeric'=>':attributeは数値でなければなりません。','integer'=>':attributeは整数でなければなりません。',
    'in'=>'選択された:attributeは無効です。','url'=>':attributeは有効なURLでなければなりません。',
    'image'=>':attributeは画像でなければなりません。','mimes'=>':attributeは:valuesタイプのファイルでなければなりません。',
    'size'=>['file'=>':attributeは:sizeキロバイトでなければなりません。'],
    'between'=>['numeric'=>':attributeは:minから:maxの間でなければなりません。'],
    'password'=>['min'=>'パスワードは:min文字以上でなければなりません。'],
    'attributes'=>['name'=>'名前','email'=>'メールアドレス','password'=>'パスワード','password_confirmation'=>'パスワード確認','title'=>'タイトル','description'=>'説明','price'=>'価格','category_id'=>'カテゴリ','pass_percentage'=>'合格率','duration_minutes'=>'時間','max_attempts'=>'最大試行回数'],
    'login_failed'=>'この認証情報は記録と一致しません。',
    'account_inactive'=>'アカウントが無効化されています。サポートにお問い合わせください。',
    'throttle'=>'ログイン試行回数が多すぎます。:seconds秒後に再試行してください。',
];
