**[ПО-РУССКИ](README-RU.md)**


# HTML tags source code generation based on configuration array


## Installation

### Composer

```shell
composer require one234ru/html-tag-generator:dev-main 
```

### Manual

The library doesn't have any dependencies. Only [HTMLtagGenerator.php](https://raw.githubusercontent.com/1234ru/html-tag-generator/main/HTMLtagGenerator.php) file is required for work.

```php
require_once 'HTMLtagGenerator.php';
```

## Usage 

Create the object passing configuration array to the constructor. Then get an HTML code treating the object as a string:

```php
$config = [ ... ];
$html_tag = new \One234ru\HTMLtagGenerator($config);
echo $html_tag;
```


## Description of configuration fields

### `tag` and `text`

`tag` is the tag's name, `div` by default.

`text` will be inserted right after opening tag.

**Examples**

Configuration:
```php
[
    'text' => 'Here is some text'
]
``` 
Result:  
```html
<div>Here is some text</div>
```

Configuration:
```php
[
    'tag' => 'span',
    'text' => 'Here is some text'
]
``` 
Result:
```html
<span>Here is some text</span>
``` 

Tags with no closing part (like `input`) will be detected automatically and processed 
appropriately.


### `attr`

`attr` is a list of attributes in a form of key-value pairs. 

All the values are encoded using 
[htmlspecialchars()](https://www.php.net/htmlspecialchars).

If the value is an array, it will be turned into JSON.

```php
[
    'text' => 'Here is some text',
    'attr' => [
        'id' => 'main',
        'class' => 'someclass',
        'style' => 'font-weight: bold',
        'data-something' => [ 'x' => 1, 'y' => 2 ]
    ]
]
```
Result (formatted for readability):

```html
<div 
 id="main"
 class="someclass"
 style="font-weight: bold"
 data-something="{&amp;quot;x&amp;quot;:1,&amp;quot;y&amp;quot;:2}"
 >
    Here is some text
</div>
```

Some attributes, like `checked`, are treated in a special way:
if the value converts to boolean `true`, only their names go to final HTML,
otherwise nothing goes anywhere:

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

This is done for disambiguation of unobvious feature deriving from HTML standard, which 
leads to `<input checked="">` or 
`<input checked=0>` eventually working the same way as `<input checked>`, i.e. the actual 
value of the attribute not affecting anything even if it is `false` or something like that, 
and the only way to discard the attribute is to exclude it completely.


### `children`

The `children` field serves for listing of children elements in the form of similar 
configurations.

Their source code will be inserted after `text`:

```php
[
    'text' => 'Did you like it?',
    'children' => [
        [
            'tag' => 'input',
            'attr' => [
                'type' => 'submit',
                'value' => 'Yes!', // Да!
            ],
        ],
        [
            'tag' => 'input',
            'attr' => [
                'type' => 'reset',
                'value' => 'No', // Нет
            ]
        ],
        [
            'tag' => 'button',
            'text' => "I don't know", // Не знаю
        ]
    ]
]
```
```html
<div>
    Did you like it?
    <input type="submit" value="Yes!">
    <input type="reset" value="No">
    <button>I don't know</button>
</div>
```

You can pass HTML as a string directly instead of an array:

```php
[
    'tag' => 'ul',
    'children' => [
        '<li>One</li>',  
        '<li>Two</li>', 
        '<li>Three</li>', 
    ]
]
```
```html
<ul>
    <li>One</li>
    <li>Two</li>
    <li>Three</li>
</ul>
```

It is not necessary to wrap the contents into tags:

```php
[
    'children' => [
        'This is the text at the beginning. ',
        '<b>This is a bold text.</b> ',
        'This is the text at the end.',
    ]
]
```
```html
<div>This is the text at the beginning. <b>This is a bold text.</b> This is the text at the end.</div>
```

Actually, the following two configurations yield the same result:

```php
[
    'text' => 'Here is some text'
]
```
```php
[
    'children' => [ 
        'Here is some text'
    ]
] 
```
```html
<div>Here is some text</div>
```

If a string is passed as a configuration, it will be used as final HTML; 
this fact is utilized when `children` are passed as strings. The following two configurations 
are equivalent:

```php
[
    'text' => 'Here is some text'
]
```
```php
'<div>Here is some text</div>'
```
Result:
```html
<div>Here is some text</div>
```


## Inheritance: normalizing configuration (`normalizeConfig()`)

When extending the class, you may define protected `normalizeConfig()` method,
which allows using configurations of non-standard structure.  

Let's say, we often use `class` attribute and would like to define it at the highest level of 
array, not inside the `attr`. In this case we need to create a child class:

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

// Same thing using the basic class:
echo new \One234ru\HTMLtagGenerator([
    'attr' => [
        'class' => 'something'
    ]
]);
```

Result:

```html
<div class="something"></div>
```
