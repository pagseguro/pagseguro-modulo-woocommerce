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

if ( ! class_exists('WC_PagSeguro_Pages')):

    class WC_PagSeguro_Pages
    {

        /**
         * Create PagSeguro Page
         *
         * @return int
         */
        public function create_pagseguro_page()
        {
            $pagseguro = array(
                'name'    => _x( 'pagseguro', 'Page slug', 'woocommerce' ),
                'title'   => _x( 'PagSeguro', 'Page title', 'woocommerce' ),
                'content' => ''
            );
            return $this->create_page($pagseguro);
        }

        /**
         * Create PagSeguro Checkout Page
         *
         * @void
         */
        public function create_pagseguro_checkout_page($parent_id)
        {
            $checkout = array(
                'name'    => _x( 'checkout', 'Page slug', 'woocommerce' ),
                'title'   => _x( 'PagSeguro Checkout', 'Page title', 'woocommerce' ),
                'content' => '[pagseguro_checkout]',
                'parent'  => $parent_id
            );
            $this->create_page($checkout);
        }

        /**
         * Create PagSeguro Direct Payment Checkout Page
         *
         * @void
         */
        public function create_pagseguro_direct_payment_checkout_page($parent_id)
        {
            $checkout = array(
                'name'    => _x( 'direct-payment', 'Page slug', 'woocommerce' ),
                'title'   => _x( 'PagSeguro Checkout Transparente', 'Page title', 'woocommerce' ),
                'content' => '[pagseguro_direct_payment]',
                'parent'  => $parent_id
            );
            $this->create_page($checkout);
        }

        /**
         * Create PagSeguro Direct Payment Checkout Page
         *
         * @void
         */
        public function create_pagseguro_order_confirmation_checkout_page($parent_id)
        {
            $checkout = array(
                'name'    => _x( 'order-confirmation', 'Page slug', 'woocommerce-pagseguro-oficial' ),
                'title'   => _x( 'PagSeguro Ordem Recebida', 'Page title', 'woocommerce-pagseguro-oficial' ),
                'content' => '[pagseguro_order_confirmation]',
                'parent'  => $parent_id
            );
            $this->create_page($checkout);
        }

        /**
         * Remove all PagSeguro pages
         */
        public function remove_pagseguro_pages()
        {
            global $wpdb;
            $wpdb->delete($wpdb->posts, $this->get_pagseguro_pages_ids());
        }

        /**
         * Get PagSeguro Pages Id's
         *
         * @return array
         */
        private function get_pagseguro_pages_ids()
        {
            global $wpdb;
            return $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_value FROM $wpdb->options WHERE 'option_name' LIKE %s", 'woocommerce_ps_pagseguro%'
                )
            );
        }

        /**
         * Create an wordpress page
         *
         * @param array $page
         * @return int
         */
        private function create_page(array $page)
        {
            return $this->wc_create_page(
                esc_sql($page['name']),
                'woocommerce_ps_' . esc_sql($page['name']) . '_page_id',
                $page['title'],
                $page['content'],
                $page['parent']
            );
        }

        /**
         * Create a page and store the ID in an option.
         *
         * @param mixed $slug Slug for the new page
         * @param string $option Option name to store the page's ID
         * @param string $page_title (default: '') Title for the new page
         * @param string $page_content (default: '') Content for the new page
         * @param int $post_parent (default: 0) Parent for the new page
         * @return int page ID
         */
        private function wc_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
            global $wpdb;

            $option_value     = get_option( $option );

            if ( $option_value > 0 ) {
                $page_object = get_post( $option_value );

                if ( 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ) ) ) {
                    // Valid page is already in place
                    return $page_object->ID;
                }
            }

            if ( strlen( $page_content ) > 0 ) {
                // Search for an existing page with the specified page content (typically a shortcode)
                $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' ) AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
            } else {
                // Search for an existing page with the specified page slug
                $valid_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status NOT IN ( 'pending', 'trash', 'future', 'auto-draft' )  AND post_name = %s LIMIT 1;", $slug ) );
            }

            $valid_page_found = apply_filters( 'woocommerce_create_page_id', $valid_page_found, $slug, $page_content );

            if ( $valid_page_found ) {
                if ( $option ) {
                    update_option( $option, $valid_page_found );
                }
                return $valid_page_found;
            }

            // Search for a matching valid trashed page
            if ( strlen( $page_content ) > 0 ) {
                // Search for an existing page with the specified page content (typically a shortcode)
                $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_content LIKE %s LIMIT 1;", "%{$page_content}%" ) );
            } else {
                // Search for an existing page with the specified page slug
                $trashed_page_found = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_type='page' AND post_status = 'trash' AND post_name = %s LIMIT 1;", $slug ) );
            }

            if ( $trashed_page_found ) {
                $page_id   = $trashed_page_found;
                $page_data = array(
                    'ID'             => $page_id,
                    'post_status'    => 'publish',
                );
                wp_update_post( $page_data );
            } else {
                $page_data = array(
                    'post_status'    => 'publish',
                    'post_type'      => 'page',
                    'post_author'    => 1,
                    'post_name'      => $slug,
                    'post_title'     => $page_title,
                    'post_content'   => $page_content,
                    'post_parent'    => $post_parent,
                    'comment_status' => 'closed'
                );
                $page_id = wp_insert_post( $page_data );
            }

            if ( $option ) {
                update_option( $option, $page_id );
            }

            return $page_id;
        }
    }

endif;