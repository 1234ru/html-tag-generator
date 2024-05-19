**[IN ENGLISH](README.md)**


# Генерация кода HTML-тегов на основе конфигурационных массивов


## Подключение


### С помощью Composer

Выполните команду `composer require`, явно указав ветку `main` при помощи префикса `dev-`:

```shell
composer require one234ru/html-tag-generator:dev-main 
```


### Вручную

Библиотека не имеет внешних зависимостей. Для работы требуется только файл 
[HTMLtagGenerator.php](https://raw.githubusercontent.com/1234ru/html-tag-generator/main/HTMLtagGenerator.php):

```php
require_once 'HTMLtagGenerator.php';
```


## Использование

Создаем объект, передавая конструктору конфигурацию. Получаем HTML, 
обращаясь с объектом как со строкой:

```php
$config = [ ... ];
$html_tag = new \One234ru\HTMLtagGenerator($config);
echo $html_tag;
```


## Описание полей конфигурации


### `tag` и `text`

`tag` — имя тега, по умолчанию — `div`

`text` — содержимое, которое будет вставлено сразу после открывающего тега.

**Примеры**

Конфигурация:
```php
[
    'text' => 'Здесь текст'
]
``` 
Результат:  
```html
<div>Здесь текст</div>
```

Конфигурация:
```php
[
    'tag' => 'span',
    'text' => 'Здесь текст'
]
``` 
Результат:
```html
<span>Здесь текст</span>
``` 

Теги без закрывающей части (например, `input`) будут определены автоматически и обработаны 
корректно.


### `attr`

`attr` — список атрибутов тега в формате ключ-значение. 

Все значения проходят кодирование с помощью [htmlspecialchars()](https://www.php.net/htmlspecialchars).

Если в качестве значения передан массив, он будет преобразован в JSON.

```php
[
    'text' => 'Здесь текст',
    'attr' => [
        'id' => 'main',
        'class' => 'someclass',
        'style' => 'font-weight: bold',
        'data-something' => [ 'x' => 1, 'y' => 2 ]
    ]
]
```
Результат (отформатирован для удобочитаемости):

```html
<div 
 id="main"
 class="someclass"
 style="font-weight: bold"
 data-something="{&amp;quot;x&amp;quot;:1,&amp;quot;y&amp;quot;:2}"
 >
    Здесь текст
</div>
```

Некоторые атрибуты, например, `checked`, обрабатываются специальным образом: 
если значение преобразуется к `true`, в HTML-код вставляются их имена,
в противном случае не вставляется ничего:

```php
[
    'tag' => 'input',
    'attr' => [
        'type' => 'checkbox',
        'checked' => true
    ]
]
```
```html
<input type="checkbox" checked>
```

```php
[
    'tag' => 'input',
    'attr' => [
        'type' => 'checkbox',
        'checked' => false
    ]
]
```
```html
<input type="checkbox">
```

Это сделано для устранения неочевидной особенности, соответствующей стандарту HTML,
из-за которой `<input checked="">` или `<input checked=0>` работают так же,
как `<input checked>`, 
то есть, значение атрибута не влияет ни на что, даже в случае, если это `false` или что-то 
подобное, и единственный способ выключить атрибут — это убрать его совсем. 



### `children`

Поле `children` служит для перечисления дочерних элементов в виде аналогичных конфигураций. 
Они будут вставлены в содержимое тега после `text`:  

```php
[
    'text' => 'Вам понравилось?',
    'children' => [
        [
            'tag' => 'input',
            'attr' => [
                'type' => 'submit',
                'value' => 'Да!',
            ],
        ],
        [
            'tag' => 'input',
            'attr' => [
                'value' => 'Нет',
                'type' => 'reset',
            ]
        ],
        [
            'tag' => 'button',
            'text' => "Не знаю"
        ]
    ]
]
```
```html
<div>
    Вам понравилось?
    <input type="submit" value="Да!">
    <input type="reset" value="Нет">
    <button>Не знаю</button>
</div>
```

Можно передавать в `children` не массивы, а прямо HTML в виде строк:

```php
[
    'tag' => 'ul',
    'children' => [
        '<li>Раз</li>',  
        '<li>Два</li>', 
        '<li>Три</li>', 
    ]
]
```
```html
<ul>
    <li>Раз</li>
    <li>Два</li>
    <li>Три</li>
</ul>
```

Оборачивать содержимое в теги необязательно:

```php
[
    'children' => [
        'Это текст в начале.',
        '<b>Это жирный текст.</b> ',
        'Это текст в конце.',
    ]
]
```
```html
<div>Это текст в начале. <b>Это жирный текст.</b> Это текст в конце.</div>
```

Вообще говоря, две следующие конфигурации дают один и тот же результат: 

```php
[
    'text' => 'Здесь текст'
]
```
```php
[
    'children' => [ 
        'Здесь текст'
    ]
] 
```
```html
<div>Здесь текст</div>
```

Если в качестве конфигурации указать не массив, а строку, эта строка будет использована 
как конечный HTML; именно этот факт используется при упрощенном перечислении 
`children` в качестве строк. Две следующие конфигурации эквивалентны:

```php
[
    'text' => 'Здесь текст'
]
```
```php
'<div>Здесь текст</div>'
```
Результат:
```html
<div>Здесь текст</div>
```


## Наследование: приведение конфигурации к стандартному виду (`normalizeConfig()`)

При наследовании можно определить защищенный метод `normalizeConfig()`, 
который позволит использовать структуру конфигурации, отличающуюся от стандартной. 

Предположим, мы часто задаем элементам атрибут `class` и хотим указывать его на верхнем 
уровне конфигурации, а не внутри `attr`. В таком случае нужно создать дочерний класс: 

```php
class Test extends \One234ru\HTMLtagGenerator {
    protected function normalizeConfig($config)
    {
        if (isset($config['class'])) {
            $config['attr']['class'] = $config['class'];
            unset($config['class']);
        }
        return $config;
    }
}

echo new Test([
    'class' => 'something',
]);

// То же с использованием базового класса:
echo new \One234ru\HTMLtagGenerator([
    'attr' => [
        'class' => 'something'
    ]
]);
```

Результат:

```html
<div class="something"></div>
```
