<?php
/**
 ************************************************************************
Copyright [2016] [PagSeguro Internet Ltda.]

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 ************************************************************************
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists('WC_PagSeguro_Shortcodes')):
    
    class WC_PagSeguro_Shortcodes
    {
        /**
         * When plugin is activated
         */
        public static function init(){
            $shortcodes = array(
                'pagseguro_checkout' => __CLASS__ . '::checkout'
            );

            foreach ( $shortcodes as $shortcode => $function ) {
                add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
            }
        }

        public static function checkout()
        {
            include(pathinfo(__DIR__)['dirname'] . '/template/checkout.php');
        }
    }

endif;