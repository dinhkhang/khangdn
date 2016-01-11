<?php

/**
 * Application level View Helper
 *
 * This file is application-wide helper file. You can put all
 * application-wide helper-related methods here.
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       app.View.Helper
 * @since         CakePHP(tm) v 0.2.9
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
//App::uses('AppHelper', 'View');

/**
 * Application helper
 *
 * Add your application-wide methods in the class below, your helpers
 * will inherit them.
 *
 * @package       app.View.Helper
 */
App::uses('HtmlHelper', 'View/Helper');

class TreeCommonHelper extends HtmlHelper {

        /**
         * Build a nested list (UL/OL) out of an associative array.
         *
         * @param array $list Set of elements to list
         * @param array $options Additional HTML attributes of the list (ol/ul) tag or if ul/ol use that as tag
         * @param array $itemOptions Additional HTML attributes of the list item (LI) tag
         * @param string $tag Type of list tag to use (ol/ul)
         * @return string The nested list
         * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/html.html#HtmlHelper::nestedList
         */
        public function nestedList($list, $options = array(), $itemOptions = array(), $tag = 'ol', $extra = array()) {
                if (is_string($options)) {
                        $tag = $options;
                        $options = array();
                }
                $items = $this->_nestedListItem($list, $options, $itemOptions, $tag, $extra);
                $ol_class = !empty($extra['ol_class']) ? $extra['ol_class'] : 'dd-list';
                $options['class'] = $ol_class;
                return sprintf($this->_tags[$tag], $this->_parseAttributes($options, null, ' ', ''), $items);
        }

        /**
         * Internal function to build a nested list (UL/OL) out of an associative array.
         *
         * @param array $items Set of elements to list
         * @param array $options Additional HTML attributes of the list (ol/ul) tag
         * @param array $itemOptions Additional HTML attributes of the list item (LI) tag
         * @param string $tag Type of list tag to use (ol/ul)
         * @return string The nested list element
         * @see HtmlHelper::nestedList()
         */
        protected function _nestedListItem($items, $options, $itemOptions, $tag, $extra = array()) {
                $out = '';
                $model_name = $extra['model_name'];
                $key = !empty($extra['key']) ? $extra['key'] : 'name';
                $div_handle_class = !empty($extra['div_handle_class']) ? $extra['div_handle_class'] : 'dd-handle';
                $li_class = !empty($extra['li_class']) ? $extra['li_class'] : 'dd-item';
                $div_handle = '<div class="' . $div_handle_class . '">%s - %s ( %s )</div>';

                $status = Configure::read('sysconfig.App.status');
                $index = 1;
                foreach ($items as $item) {

                        $item_status = __('unknown');
                        if (
                                !empty($item['children'])
                        ) {
                                $item_out = sprintf($div_handle, $index, $item[$model_name][$key], $item_status) . $this->nestedList($item, $options, $itemOptions, $tag, $extra);
                        } else {

                                $item_out = sprintf($div_handle, $index, $item[$model_name][$key], $item_status);
                        }
                        if (isset($itemOptions['even']) && $index % 2 === 0) {
                                $itemOptions['class'] = $itemOptions['even'];
                        } elseif (isset($itemOptions['odd']) && $index % 2 !== 0) {
                                $itemOptions['class'] = $itemOptions['odd'];
                        }
                        $itemOptions['data-id'] = $index;
                        $itemOptions['class'] = $li_class;
                        $out .= sprintf($this->_tags['li'], $this->_parseAttributes($itemOptions, array('even', 'odd'), ' ', ''), $item_out);
                        $index++;
                }
                return $out;
        }

}
