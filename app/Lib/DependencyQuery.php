<?php

class DependencyQuery {

        public $query;

        public function __construct($query) {
                $this->query = $query;
        }

        public function isArray($name) {
                if (isset($this->query[$name]) && is_array($this->query[$name])) {
                        return $this;
                } else {
                        return FALSE;
                }
        }

        public function isString($name) {
                if (isset($this->query[$name]) && strlen($this->query[$name])) {
                        return $this;
                } else {
                        // check modified start end
                        if (isset($this->query[$name . '_start']) || isset($this->query[$name . '_end'])) {
                                return $this;
                        } else {
                                return FALSE;
                        }
                }
        }

        public function isDateTime($name) {
                if (isset($this->query[$name]) && is_numeric(strpos($this->query[$name], ':'))) {
                        return $this;
                } else {
                        return FALSE;
                }
        }

}
