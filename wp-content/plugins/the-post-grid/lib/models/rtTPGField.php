<?php

if (!class_exists('rtTPGField')):
    class rtTPGField
    {
        private $type;
        private $name;
        private $value;
        private $default;
        private $label;
        private $id;
        private $class;
        private $holderClass;
        private $description;
        private $options;
        private $option;
        private $attr;
        private $multiple;
        private $alignment;
        private $placeholder;

        function __construct() {
        }

        private function setArgument($attr) {
            $this->type = isset($attr['type']) ? ($attr['type'] ? $attr['type'] : 'text') : 'text';
            $this->multiple = isset($attr['multiple']) ? ($attr['multiple'] ? $attr['multiple'] : false) : false;
            $this->name = isset($attr['name']) ? ($attr['name'] ? $attr['name'] : null) : null;
            $this->default = isset($attr['default']) ? ($attr['default'] ? $attr['default'] : null) : null;
            $this->value = isset($attr['value']) ? ($attr['value'] ? $attr['value'] : null) : null;

            if (!$this->value) {
                if ($this->multiple) {
                    $v = get_post_meta(get_the_ID(), $this->name);
                } else {
                    $v = get_post_meta(get_the_ID(), $this->name, true);
                }
                $this->value = ($v ? $v : $this->default);
            }

            $this->label = isset($attr['label']) ? ($attr['label'] ? $attr['label'] : null) : null;
            $this->id = isset($attr['id']) ? ($attr['id'] ? $attr['id'] : null) : null;
            $this->class = isset($attr['class']) ? ($attr['class'] ? $attr['class'] : null) : null;
            $this->holderClass = isset($attr['holderClass']) ? ($attr['holderClass'] ? $attr['holderClass'] : null) : null;
            $this->placeholder = isset($attr['placeholder']) ? ($attr['placeholder'] ? $attr['placeholder'] : null) : null;
            $this->description = isset($attr['description']) ? ($attr['description'] ? $attr['description'] : null) : null;
            $this->options = isset($attr['options']) ? ($attr['options'] ? $attr['options'] : array()) : array();
            $this->option = isset($attr['option']) ? ($attr['option'] ? $attr['option'] : null) : null;
            $this->attr = isset($attr['attr']) ? ($attr['attr'] ? $attr['attr'] : null) : null;
            $this->alignment = isset($attr['alignment']) ? ($attr['alignment'] ? $attr['alignment'] : null) : null;
            $this->class = $this->class ? $this->class . " rt-form-control" : "rt-form-control";
        }

        public function Field($attr) {
            $this->setArgument($attr);
            $holderId = $this->name . "_holder";
            $html = null;
            $html .= "<div class='rt-field-wrapper {$this->holderClass}' id='{$holderId}'>";
            $html .= sprintf('<div class="rt-label">%s</div>',
                $this->label ? sprintf('<label for="">%s</label>', $this->label) : ''
            );
            $html .= "<div class='rt-field'>";
            switch ($this->type) {
                case 'text':
                    $html .= $this->text();
                    break;

                case 'url':
                    $html .= $this->url();
                    break;

                case 'number':
                    $html .= $this->number();
                    break;

                case 'select':
                    $html .= $this->select();
                    break;

                case 'textarea':
                    $html .= $this->textArea();
                    break;

                case 'checkbox':
                    $html .= $this->checkbox();
                    break;

                case 'radio':
                    $html .= $this->radioField();
                    break;

                case 'custom_css':
                    $html .= $this->customCss();
                    break;
            }
            if ($this->description) {
                $html .= "<p class='description'>{$this->description}</p>";
            }
            $html .= "</div>"; // field
            $html .= "</div>"; // field holder

            return $html;
        }

        private function text() {
            $h = null;
            $h .= "<input
                    type='text'
                    class='{$this->class}'
                    id='{$this->id}'
                    value='{$this->value}'
                    name='{$this->name}'
                    placeholder='{$this->placeholder}'
                    {$this->attr}
                    />";
            return $h;
        }

        private function customCss() {
            $h = null;
            $h .= '<div class="rt-custom-css">';
            $h .= '<div class="custom_css_container">';
            $h .= "<div name='{$this->name}' id='ret-" . mt_rand() . "' class='custom-css'>";
            $h .= '</div>';
            $h .= '</div>';

            $h .= "<textarea
                        style='display: none;'
                        class='custom_css_textarea'
                        id='{$this->id}'
                        name='{$this->name}'
                        >{$this->value}</textarea>";
            $h .= '</div>';

            return $h;
        }

        private function url() {
            $h = null;
            $h .= "<input
                    type='url'
                    class='{$this->class}'
                    id='{$this->id}'
                    value='{$this->value}'
                    name='{$this->name}'
                    placeholder='{$this->placeholder}'
                    {$this->attr}
                    />";
            return $h;
        }

        private function number() {
            $h = null;
            $h .= "<input
                    type='number'
                    class='{$this->class}'
                    id='{$this->id}'
                    value='{$this->value}'
                    name='{$this->name}'
                    placeholder='{$this->placeholder}'
                    {$this->attr}
                    />";
            return $h;
        }

        private function select() {
            $h = null;
            if ($this->multiple) {
                $this->attr = " style='min-width:160px;'";
                $this->name = $this->name . "[]";
                $this->attr = $this->attr . " multiple='multiple'";
                $this->value = (is_array($this->value) && !empty($this->value) ? $this->value : array());
            } else {
                $this->value = array($this->value);
            }

            $h .= "<select name='{$this->name}' id='{$this->id}' class='{$this->class}' {$this->attr}>";
            if (is_array($this->options) && !empty($this->options)) {
                foreach ($this->options as $key => $value) {
                    $slt = (in_array($key, $this->value) ? "selected" : null);
                    $h .= "<option {$slt} value='{$key}'>{$value}</option>";
                }
            }
            $h .= "</select>";
            return $h;
        }

        private function textArea() {
            $h = null;
            $h .= "<textarea
                    class='{$this->class} rt-textarea'
                    id='{$this->id}'
                    name='{$this->name}'
                    placeholder='{$this->placeholder}'
                    {$this->attr}
                    >{$this->value}</textarea>";
            return $h;
        }

        private function checkbox() {
            $h = null;
            if ($this->multiple) {
                $this->name = $this->name . "[]";
                $this->value = (is_array($this->value) && !empty($this->value) ? $this->value : array());
            }
            if ($this->multiple) {
                $h .= "<div class='checkbox-group {$this->alignment}' id='{$this->id}'>";
                if (is_array($this->options) && !empty($this->options)) {
                    foreach ($this->options as $key => $value) {
                        $checked = (in_array($key, $this->value) ? "checked" : null);
                        $h .= "<label for='{$this->id}-{$key}'>
                                <input type='checkbox' id='{$this->id}-{$key}' {$checked} name='{$this->name}' value='{$key}'>{$value}
                                </label>";
                    }
                }
                $h .= "</div>";
            } else {
                $checked = ($this->value ? "checked" : null);
                $h .= "<label><input type='checkbox' {$checked} id='{$this->id}' name='{$this->name}' value='1' />{$this->option}</label>";
            }
            return $h;
        }

        private function radioField() {
            $h = null;
            $h .= "<div class='radio-group {$this->alignment}' id='{$this->id}'>";
            if (is_array($this->options) && !empty($this->options)) {
                foreach ($this->options as $key => $value) {
                    $checked = ($key == $this->value ? "checked" : null);
                    $h .= "<label for='{$this->id}-{$key}'>
                            <input type='radio' id='{$this->id}-{$key}' {$checked} name='{$this->name}' value='{$key}'>{$value}
                            </label>";
                }
            }
            $h .= "</div>";
            return $h;
        }

    }
endif;