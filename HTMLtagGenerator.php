<?php

namespace One234ru;

class HTMLtagGenerator
{
    private const DEFAULT_CONFIG = [
        'tag' => 'div',
    ];

    private const SINGLE_TAGS = [
        'br',
        'hr',
        'input',
    ];

    private const BOOLEAN_ATTRIBUTES = [
        // Split by tag to speed the search up.
        'a' => [ 'nofollow', 'noindex' ],
        'button' => [ 'disabled' ],
        'input' => [ 'checked', 'disabled', 'required' ],
        'option' => [ 'selected' ],
        'select' => [ 'disabled', 'multiple', 'required' ],
        'textarea' => [ 'disabled', 'required' ],
    ];

    /** @var string|array = [
     *  'tag' => 'div',
     *  'attr' => [
     *      'attrName' => bool|string|array
     *  ],
     *  'text' => string,
     *  'children' => array,
     * ]
     */
    private $config;

    /** @var string */
    private $HTML;

    /**
     * @param string|array $config {@see $config}
     */
    public function __construct($config) {
        if (is_array($config)) {
            $this->config = $this->normalizeConfig($config);
            $this->normalizeStandardConfig();
            $this->initializeChildren();
        } else {
            $this->config = $config;
        }
    }

    public function __toString() {
        if (is_null($this->HTML)) {
            $this->HTML = $this->generate($this->config);
        }
        return $this->HTML;
    }

    /**
     * Method for re-defining in a child class.
     * It is not static, because it may need an access to class' properties.
     */
    protected function normalizeConfig($cfg) {
        return $cfg;
    }

    private function normalizeStandardConfig() {
        $cfg = &$this->config;
        if (!is_array($cfg)) {
            $cfg = [
                'template' => $cfg
            ];
        }
        self::convertTextToChild($cfg);
        $cfg += self::DEFAULT_CONFIG;
    }

    static private function convertTextToChild(array &$config) {
        // A 'text' parameter must be converted to a child
        // for wrapWith() method to work correctly.
        if (isset($config['text'])) {
            if (!isset($config['children'])) {
                $config['children'] = [];
            }
            array_unshift($config['children'], $config['text']);
            unset($config['text']);
        }
    }

    private function initializeChildren() {
        $children = &$this->config['children'];
        if ($children) {
            foreach ($children as &$child) {
                if (!is_a($child, __CLASS__)) {
                    $child = new self($child);
                }
            }
        }
    }

    private static function generate($data) :string {
        if (!is_string($data)) {
            $html = '<' . $data['tag'];
            foreach ($data['attr'] ?? [] as $name => $value) {
                $html .= self::attrHTML($name, $value, $data['tag']);
            }
            $html .= '>';
            if (!self::isTagSingle($data['tag'])) {
                $html .= $data['text'] ?? '';
                foreach ($data['children'] ?? [] as $child) {
                    $html .= $child;
                }
                $html .= '</' . $data['tag'] . '>';
            }
        } else {
            $html = $data;
        }
        return $html;
    }

    private static function attrHTML(
        string $attr_name,
        $attr_value,
        string $tag_name
    ) :string {
        if (!self::isAttributeBoolean($tag_name, $attr_name)) {
            $html = ' ' . $attr_name
                . '="' . self:: encodeAttributeValue($attr_value) . '"';
        } else {
            $html = ($attr_value) ? " $attr_name" : '';
        }
        return $html;
    }

    private static function isAttributeBoolean(string $tag_name, string $attr_name) :bool {
        return in_array($attr_name, self::BOOLEAN_ATTRIBUTES[$tag_name] ?? []);
    }

    private static function encodeAttributeValue($value) :string {
        if (is_array($value)) {
            $value = json_encode($value);
        } elseif (is_null($value)) {
            return '';
        }
        return htmlspecialchars($value);
    }

    private static function isTagSingle(string $name) :bool {
        return in_array($name, self::SINGLE_TAGS);
    }

    // /**
    //  * @param array $config {@see $config}
    //  * @param int|string|callable $mode
    //  */
    // final public function wrapWith($config, $mode = 0) :?self {
    //     if (is_callable($mode)) {
    //         $cfg = $mode($config, $this);
    //     } else {
    //         $cfg = $config;
    //         $children = &$cfg['children'];
    //         if (!is_array($children)) {
    //             $children = [];
    //         }
    //         if ($mode === 'append') {
    //             $children[] = $this;
    //         } elseif (is_numeric($mode)) {
    //             $index = $mode;
    //             $children = array_merge(
    //                 array_slice($children, 0, $index),
    //                 [ $this ],
    //                 array_slice($children, $index)
    //             );
    //         } else {
    //             $msg = "Incorrect mode given: " . var_dump($mode) . "\n"
    //                 . "Only 'append', child's number or callback are allowed.";
    //             trigger_error($msg, E_USER_WARNING);
    //             return null;
    //         }
    //     }
    //
    //     return new self($cfg);
    // }

}