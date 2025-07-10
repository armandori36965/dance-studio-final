<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => '必須接受 :attribute。',
    'active_url' => ':attribute 不是有效的網址。',
    'after' => ':attribute 必須是 :date 之後的日期。',
    'after_or_equal' => ':attribute 必須是 :date 之後或相同的日期。',
    'alpha' => ':attribute 只能包含字母。',
    'alpha_dash' => ':attribute 只能包含字母、數字、破折號和底線。',
    'alpha_num' => ':attribute 只能包含字母和數字。',
    'array' => ':attribute 必須是個陣列。',
    'before' => ':attribute 必須是 :date 之前的日期。',
    'before_or_equal' => ':attribute 必須是 :date 之前或相同的日期。',
    'between' => [
        'numeric' => ':attribute 必須介於 :min 和 :max之間。',
        'file' => ':attribute 必須介於 :min 和 :max KB之間。',
        'string' => ':attribute 必須介於 :min 和 :max 個字元之間。',
        'array' => ':attribute 必須介於 :min 和 :max 個項目之間。',
    ],
    'boolean' => ':attribute 欄位必須是 true 或 false。',
    'confirmed' => ':attribute 確認不符。',
    'date' => ':attribute 不是個有效的日期。',
    'date_equals' => ':attribute 必須是等於 :date 的日期。',
    'date_format' => ':attribute 不符合 :format 的格式。',
    'different' => ':attribute 和 :other 必須不同。',
    'digits' => ':attribute 必須是 :digits 位數字。',
    'digits_between' => ':attribute 必須介於 :min 和 :max 位數字之間。',
    'dimensions' => ':attribute 圖片尺寸不正確。',
    'distinct' => ':attribute 欄位有重複的值。',
    'email' => ':attribute 必須是個有效的電子郵件。',
    'ends_with' => ':attribute 必須以下列之一結尾: :values',
    'exists' => '所選擇的 :attribute 無效。',
    'file' => ':attribute 必須是個檔案。',
    'filled' => ':attribute 欄位必須有值。',
    'gt' => [
        'numeric' => ':attribute 必須大於 :value。',
        'file' => ':attribute 必須大於 :value KB。',
        'string' => ':attribute 必須多於 :value 個字元。',
        'array' => ':attribute 必須多於 :value 個項目。',
    ],
    'gte' => [
        'numeric' => ':attribute 必須大於等於 :value。',
        'file' => ':attribute 必須大於等於 :value KB。',
        'string' => ':attribute 必須多於等於 :value 個字元。',
        'array' => ':attribute 必須多於等於 :value 個項目。',
    ],
    'image' => ':attribute 必須是張圖片。',
    'in' => '所選擇的 :attribute 無效。',
    'in_array' => ':attribute 欄位不存在於 :other。',
    'integer' => ':attribute 必須是個整數。',
    'ip' => ':attribute 必須是個有效的 IP 位址。',
    'ipv4' => ':attribute 必須是個有效的 IPv4 位址。',
    'ipv6' => ':attribute 必須是個有效的 IPv6 位址。',
    'json' => ':attribute 必須是個有效的 JSON 字串。',
    'lt' => [
        'numeric' => ':attribute 必須小於 :value。',
        'file' => ':attribute 必須小於 :value KB。',
        'string' => ':attribute 必須少於 :value 個字元。',
        'array' => ':attribute 必須少於 :value 個項目。',
    ],
    'lte' => [
        'numeric' => ':attribute 必須小於等於 :value。',
        'file' => ':attribute 必須小於等於 :value KB。',
        'string' => ':attribute 必須少於等於 :value 個字元。',
        'array' => ':attribute 必須少於等於 :value 個項目。',
    ],
    'max' => [
        'numeric' => ':attribute 不能大於 :max。',
        'file' => ':attribute 不能大於 :max KB。',
        'string' => ':attribute 不能多於 :max 個字元。',
        'array' => ':attribute 最多有 :max 個項目。',
    ],
    'mimes' => ':attribute 必須是 :values 類型的檔案。',
    'mimetypes' => ':attribute 必須是 :values 類型的檔案。',
    'min' => [
        'numeric' => ':attribute 不能小於 :min。',
        'file' => ':attribute 不能小於 :min KB。',
        'string' => ':attribute 不能少於 :min 個字元。',
        'array' => ':attribute 最少有 :min 個項目。',
    ],
    'not_in' => '所選擇的 :attribute 無效。',
    'not_regex' => ':attribute 的格式無效。',
    'numeric' => ':attribute 必須是個數字。',
    'password' => '密碼不正確。',
    'present' => ':attribute 欄位必須存在。',
    'regex' => ':attribute 的格式無效。',
    'required' => ':attribute 欄位為必填。',
    'required_if' => '當 :other 是 :value 時，:attribute 欄位為必填。',
    'required_unless' => '當 :other 不在 :values 時，:attribute 欄位為必填。',
    'required_with' => '當 :values存在時，:attribute 欄位為必填。',
    'required_with_all' => '當 :values 都存在時，:attribute 欄位為必填。',
    'required_without' => '當 :values 不存在時，:attribute 欄位為必填。',
    'required_without_all' => '當 :values 都不存在時，:attribute 欄位為必填。',
    'same' => ':attribute 和 :other 必須相符。',
    'size' => [
        'numeric' => ':attribute 必須是 :size。',
        'file' => ':attribute 必須是 :size KB。',
        'string' => ':attribute 必須是 :size 個字元。',
        'array' => ':attribute 必須包含 :size 個項目。',
    ],
    'starts_with' => ':attribute 必須以下列之一開頭: :values',
    'string' => ':attribute 必須是字串。',
    'timezone' => ':attribute 必須是個有效的時區。',
    'unique' => ':attribute 已存在。', // <--- 這就是您要的翻譯
    'uploaded' => ':attribute 上傳失敗。',
    'url' => ':attribute 的格式無效。',
    'uuid' => ':attribute 必須是個有效的 UUID。',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => '名稱',
        'phone_number' => '電話',
        'color' => '代表色',
        'price' => '價格',
        // 可以繼續添加其他欄位的中英文對照
    ],
];