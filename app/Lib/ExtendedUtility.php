<?php

class ExtendedUtility {

        static public function array_intersect_key_recursive(array $array1, array $array2) {

                $array1 = array_intersect_key($array1, $array2);

                foreach ($array1 as $key => &$value) {

                        if (is_array($value) && is_array($array2[$key])) {

                                $value = self::array_intersect_key_recursive($value, $array2[$key]);
                        }
                }

                return $array1;
        }

}
