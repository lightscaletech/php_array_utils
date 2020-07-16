<?php

namespace Lightscale;

class ArrayUtils {

    public static function get($arr, $path, $def = NULL) {
        if(!$arr) return $def;
        if(is_string($path) || is_integer($path)) return self::get($arr, [$path], $def);
        if(!is_array($path)) return $def;

        $k = array_shift($path);
        $v = (is_array($arr) && isset($arr[$k])) ? $arr[$k] :
             ((is_object($arr) && isset($arr->$k)) ? $arr->$k : $def);
        if(isset($path[0])) {
            return self::get($v, $path, $def);
        }
        return $v;
    }

    public static function getter($data, $sanitize = 'sanitize_text_field') {
        $get = function($k = NULL, $d = NULL, $san = null) use(&$get, $data, $sanitize) {
            $san = $san === NULL ? $sanitize : $san;

            if($k === null) return $get(array_keys($data), $d, $san);
            if(is_array($k)) return array_reduce($k, function($r, $k) use(&$get, $d, $san) {
                $vsan = null;
                if(is_array($k)) {
                    $vsan = $k[1];
                    $k = $k[0];
                }
                $final_san = $vsan === null ? $san : $vsan;
                $r[$k] = $get($k, $d, $final_san);
                return $r;
            }, []);

            $san = $san === false ? function($v){ return $v; } : $san;
            $res = self::get($data, $k, $d);
            return is_callable($san) ? $san($res) : $res;
        };
        return $get;
    }

    public static function select($arr, $selection) {
        $selection = is_string($selection) ? [$selection] : $selection;
        return array_reduce($selection, function($res, $itm) use($arr) {
            $res[$itm] = self::get($arr, $itm);
            return $res;
        }, []);
    }

    public static function dissoc($arr, $selection) {
        $selection = is_string($selection) ? [$selection] : $selection;
        return array_reduce(array_keys($arr), function($r, $k) use($arr, $selection) {
            if(!in_array($k, $selection)) $r[$k] = $arr[$k];
            return $r;
        }, []);
    }

    public static function flatten($arr) {
        return array_reduce($arr, function($r, $i) {
            if(is_array($i)) return array_merge($r, self::flatten($i));
            else $r[] = $i;
            return $r;
        }, []);
    }

    public static function topairs($arr) {
        return array_reduce(array_keys($arr), function($r, $k) use($arr) {
            $r[] = [$k, $arr[$k]];
            return $r;
        }, []);
    }

    public static function updatein($arr, $path, $fn) {
        $path = is_string($path) ? [$path] : $path;
        if(!is_array($arr)) $arr = [];
        $data = &$arr;
        foreach($path as $k) {
            if(!isset($data[$k])) {
                $data[$k] = NULL;
            }
            $data = &$data[$k];
        }
        $data = $fn($data);
        return $arr;
    }

    public static function anyo($arr, $order, $fn) {
        $res = null;
        foreach($order as $k) {
            $res = $fn(self::get($arr, $k, null));
            if($res !== false && $res !== null)
                return $res;
        }
    }

    public static function any($a, $f) {
        return self::anyo($a, array_keys($a), $f);
    }

    public static function groupBy(array $a, string $k) {
        $r = [];

        foreach($a as $itm) {
            $by = self::get($itm, $k);
            $col = self::get($r, $by, []);
            $col[] = $itm;
            $r[$by] = $col;
        }

        return $r;
    }

}
