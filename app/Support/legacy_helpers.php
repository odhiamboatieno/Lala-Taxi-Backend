<?php

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

if (! function_exists('str_random')) {
    function str_random($length = 16)
    {
        return Str::random($length);
    }
}

if (! function_exists('str_slug')) {
    function str_slug($title, $separator = '-', $language = 'en')
    {
        return Str::slug($title, $separator, $language);
    }
}

if (! function_exists('array_get')) {
    function array_get($array, $key, $default = null)
    {
        return Arr::get($array, $key, $default);
    }
}

if (! class_exists('Html')) {
    class Html
    {
        public static function script($url, $attributes = [])
        {
            return new HtmlString('<script src="'.e(asset($url)).'"'.self::attributes($attributes).'></script>');
        }

        public static function style($url, $attributes = [])
        {
            return new HtmlString('<link rel="stylesheet" href="'.e(asset($url)).'"'.self::attributes($attributes).'>');
        }

        public static function image($url, $alt = null, $attributes = [])
        {
            return new HtmlString('<img src="'.e(asset($url)).'" alt="'.e($alt ?? '').'"'.self::attributes($attributes).'>');
        }

        public static function link($url, $title = null, $attributes = [], $secure = null, $escape = true)
        {
            $body = $escape ? e($title ?? $url) : ($title ?? $url);

            return new HtmlString('<a href="'.e(url($url)).'"'.self::attributes($attributes).'>'.$body.'</a>');
        }

        public static function attributes($attributes)
        {
            $html = '';

            foreach ((array) $attributes as $key => $value) {
                if (is_bool($value)) {
                    if ($value) {
                        $html .= ' '.e($key);
                    }
                    continue;
                }

                if ($value !== null) {
                    $html .= ' '.e($key).'="'.e(self::stringValue($value)).'"';
                }
            }

            return $html;
        }

        public static function stringValue($value)
        {
            if ($value instanceof HtmlString) {
                return $value->toHtml();
            }

            if ($value instanceof BackedEnum) {
                return $value->value;
            }

            if ($value instanceof UnitEnum) {
                return $value->name;
            }

            if (is_array($value)) {
                foreach (['name', 'label', 'title', 'text', 'value', 'code', 'id'] as $key) {
                    if (array_key_exists($key, $value) && ! is_array($value[$key]) && ! is_object($value[$key])) {
                        return $value[$key];
                    }
                }

                return implode(', ', array_map([self::class, 'stringValue'], Arr::flatten($value)));
            }

            if (is_object($value)) {
                foreach (['name', 'label', 'title', 'text', 'value', 'code', 'id'] as $key) {
                    if (isset($value->{$key}) && ! is_array($value->{$key}) && ! is_object($value->{$key})) {
                        return $value->{$key};
                    }
                }

                if (method_exists($value, '__toString')) {
                    return (string) $value;
                }

                if (method_exists($value, 'toArray')) {
                    return self::stringValue($value->toArray());
                }

                return get_class($value);
            }

            return (string) $value;
        }
    }
}

if (! class_exists('Form')) {
    class Form
    {
        public static function open($options = [])
        {
            $method = strtoupper($options['method'] ?? 'POST');
            $actualMethod = in_array($method, ['GET', 'POST'], true) ? $method : 'POST';
            $attributes = self::formAttributes($options, $actualMethod);
            $html = '<form'.Html::attributes($attributes).'>';

            if ($actualMethod === 'POST') {
                $html .= csrf_field();
            }

            if ($method !== $actualMethod) {
                $html .= method_field($method);
            }

            return new HtmlString($html);
        }

        public static function close()
        {
            return new HtmlString('</form>');
        }

        public static function token()
        {
            return new HtmlString(csrf_field());
        }

        public static function hidden($name, $value = null, $attributes = [])
        {
            return self::input('hidden', $name, $value, $attributes);
        }

        public static function text($name, $value = null, $attributes = [])
        {
            return self::input('text', $name, $value, $attributes);
        }

        public static function email($name, $value = null, $attributes = [])
        {
            return self::input('email', $name, $value, $attributes);
        }

        public static function password($name, $attributes = [])
        {
            return self::input('password', $name, null, $attributes);
        }

        public static function tel($name, $value = null, $attributes = [])
        {
            return self::input('tel', $name, $value, $attributes);
        }

        public static function number($name, $value = null, $attributes = [])
        {
            return self::input('number', $name, $value, $attributes);
        }

        public static function file($name, $attributes = [])
        {
            return self::input('file', $name, null, $attributes);
        }

        public static function radio($name, $value = null, $checked = false, $attributes = [])
        {
            return self::checkable('radio', $name, $value, $checked, $attributes);
        }

        public static function checkbox($name, $value = 1, $checked = false, $attributes = [])
        {
            return self::checkable('checkbox', $name, $value, $checked, $attributes);
        }

        public static function textarea($name, $value = null, $attributes = [])
        {
            $attributes = array_merge(['name' => $name], $attributes);

            return new HtmlString('<textarea'.Html::attributes($attributes).'>'.e(Html::stringValue($value ?? '')).'</textarea>');
        }

        public static function select($name, $list = [], $selected = null, $attributes = [])
        {
            if (is_object($list) && method_exists($list, 'toArray')) {
                $list = $list->toArray();
            }

            $placeholder = $attributes['placeholder'] ?? null;
            unset($attributes['placeholder']);
            $attributes = array_merge(['name' => $name], $attributes);
            $html = '<select'.Html::attributes($attributes).'>';

            if ($placeholder !== null) {
                $html .= '<option value="">'.e(Html::stringValue($placeholder)).'</option>';
            }

            foreach ((array) $list as $value => $label) {
                if (is_array($label) && Arr::isAssoc($label)) {
                    $html .= '<optgroup label="'.e(Html::stringValue($value)).'">';
                    foreach ($label as $groupValue => $groupLabel) {
                        $isSelected = (string) $groupValue === (string) $selected ? ' selected' : '';
                        $html .= '<option value="'.e(Html::stringValue($groupValue)).'"'.$isSelected.'>'.e(Html::stringValue($groupLabel)).'</option>';
                    }
                    $html .= '</optgroup>';
                    continue;
                }

                $isSelected = (string) $value === (string) $selected ? ' selected' : '';
                $html .= '<option value="'.e(Html::stringValue($value)).'"'.$isSelected.'>'.e(Html::stringValue($label)).'</option>';
            }

            return new HtmlString($html.'</select>');
        }

        protected static function input($type, $name, $value = null, $attributes = [])
        {
            $attributes = array_merge(['type' => $type, 'name' => $name], $attributes);

            if ($value !== null) {
                $attributes['value'] = $value;
            }

            return new HtmlString('<input'.Html::attributes($attributes).'>');
        }

        protected static function checkable($type, $name, $value = null, $checked = false, $attributes = [])
        {
            $attributes = array_merge(['type' => $type, 'name' => $name], $attributes);

            if ($value !== null) {
                $attributes['value'] = $value;
            }

            if ($checked) {
                $attributes['checked'] = true;
            }

            return new HtmlString('<input'.Html::attributes($attributes).'>');
        }

        protected static function formAttributes($options, $method)
        {
            $attributes = Arr::except($options, ['url', 'route', 'action', 'method', 'files']);
            $attributes['method'] = $method;

            if (isset($options['url'])) {
                $attributes['action'] = url($options['url']);
            } elseif (isset($options['route'])) {
                $route = is_array($options['route']) ? $options['route'] : [$options['route']];
                $attributes['action'] = route(...$route);
            } elseif (isset($options['action'])) {
                $action = is_array($options['action']) ? $options['action'] : [$options['action']];
                $attributes['action'] = action(...$action);
            }

            if (! empty($options['files'])) {
                $attributes['enctype'] = 'multipart/form-data';
            }

            return $attributes;
        }
    }
}

if (! class_exists('Excel')) {
    class Excel
    {
        public static function create($filename, callable $callback)
        {
            $export = new LegacyCsvExport($filename);
            $callback($export);

            return $export;
        }
    }
}

if (! class_exists('LegacyCsvExport')) {
    class LegacyCsvExport
    {
        protected $filename;
        protected $rows = [];

        public function __construct($filename)
        {
            $this->filename = $filename;
        }

        public function sheet($name, callable $callback)
        {
            $sheet = new LegacyCsvSheet;
            $callback($sheet);
            $this->rows = array_merge($this->rows, $sheet->rows());

            return $this;
        }

        public function export($format)
        {
            return $this->download($format);
        }

        public function download($format = 'csv')
        {
            $filename = $this->filename.'.'.strtolower($format ?: 'csv');

            return response()->streamDownload(function () {
                $output = fopen('php://output', 'w');

                foreach ($this->rows as $row) {
                    fputcsv($output, (array) $row);
                }

                fclose($output);
            }, $filename)->send();
        }
    }
}

if (! class_exists('LegacyCsvSheet')) {
    class LegacyCsvSheet
    {
        protected $rows = [];

        public function with($rows)
        {
            return $this->fromArray($rows);
        }

        public function fromArray($rows)
        {
            $this->rows = array_values((array) $rows);

            return $this;
        }

        public function setWidth($width)
        {
            return $this;
        }

        public function setAllBorders($style)
        {
            return $this;
        }

        public function rows()
        {
            return $this->rows;
        }
    }
}

if (! function_exists('shouldShowRating')) {
    function shouldShowRating($trip_id)
    {
        return resolve(\App\Services\Rating\DriverRatingEligibilityService::class)
            ->shouldShowRating((int) $trip_id);
    }
}
