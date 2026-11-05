<?php

return [
    'after' => 'Поле «:attribute» должно быть позже, чем «:date».',
    'after_or_equal' => 'Поле «:attribute» должно быть не раньше :date.',
    'array' => 'Поле «:attribute» должно быть списком.',
    'before_or_equal' => 'Поле «:attribute» должно быть не позже :date.',
    'date_format' => 'Поле «:attribute» должно соответствовать формату :format.',
    'distinct' => 'Поле «:attribute» содержит повторяющееся значение.',
    'exists' => 'Выбранное значение поля «:attribute» не найдено.',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'max' => [
        'array' => 'Поле «:attribute» должно содержать не более :max элементов.',
        'string' => 'Поле «:attribute» должно быть не длиннее :max символов.',
    ],
    'min' => [
        'array' => 'Поле «:attribute» должно содержать хотя бы :min элемент.',
    ],
    'required' => 'Поле «:attribute» обязательно для заполнения.',
    'string' => 'Поле «:attribute» должно быть строкой.',

    'custom' => [
        'employee_id' => [
            'exists' => 'Сотрудник не найден в этом графике.',
        ],
        'cells.*.employee_id' => [
            'exists' => 'Сотрудник не найден в этом графике.',
        ],
        'date' => [
            'after_or_equal' => 'Дата смены выходит за период графика.',
            'before_or_equal' => 'Дата смены выходит за период графика.',
        ],
        'cells.*.date' => [
            'after_or_equal' => 'Дата смены выходит за период графика.',
            'before_or_equal' => 'Дата смены выходит за период графика.',
        ],
        'end_time' => [
            'after' => 'Время окончания смены должно быть позже времени начала.',
        ],
    ],

    'attributes' => [
        'name' => 'Название',
        'address' => 'Адрес',
        'month' => 'Месяц',
        'employee_ids' => 'Сотрудники',
        'employee_id' => 'Сотрудник',
        'date' => 'Дата',
        'start_time' => 'Начало',
        'end_time' => 'Конец',
        'shift_template_id' => 'Шаблон смены',
        'cells' => 'Ячейки',
        'from' => 'С',
        'to' => 'По',
    ],
];
