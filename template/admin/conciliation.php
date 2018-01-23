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
?>
<div class="wpbody-content" id="pg-app" data-url-search="<?php echo get_home_url().'/index.php/wp-content/plugins/woocommerce-pagseguro-oficial/woocommerce-pagseguro-oficial.php?ajax=true';?>" data-url-execute="<?php echo get_home_url().'/index.php/wp-content/plugins/woocommerce-pagseguro-oficial/woocommerce-pagseguro-oficial.php?ajax=true';?>">
    <div class="wrap">
        <h1>Conciliação</h1>
        <p>
            Com esta funcionalidade você poderá estornar os valores de transações que estejam nos status “Paga”,
            “Disponível” e “Em disputa”. É aconselhável que antes de usar esta funcionalidade você faça a conciliação
            de suas transações para obter os status mais atuais.
        </p>
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <select name="action" id="wp-pagseguro-conciliation-search">
                    <option value="5" selected="selected">5</option>
                    <option value="10">10</option>
                    <option value="15">15</option>
                    <option value="20">20</option>
                    <option value="25">25</option>
                    <option value="30">30</option>
                </select>
                <input type="submit" id="wp-pagseguro-conciliation-button-search" class="button action" value="Pesquisar" />
            </div>
            <div class="alignright actions bulkactions">
                <label for="bulk-action-selector-top" class="screen-reader-text">Select bulk action</label>
                <input type="submit" id="wp-pagseguro-conciliation-execute" class="button action" value="Conciliar" />
            </div>
            <br class="clear">
        </div><!-- /.tablenav -->
        <table id="wp-pagseguro-conciliation-table" class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th class="manage-column" style="width: 5%"><input  class="check-all" type="checkbox"/></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Data</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>ID WooCommerce</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>ID PagSeguro</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Status WooCommerce</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Status PagSeguro</a></th>
                    <th class="manage-column column-title" style="width: 5%">Ação</th>
                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="manage-column" style="width: 5%"><input class="check-all" type="checkbox"/></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Data</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>ID WooCommerce</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>ID PagSeguro</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Status WooCommerce</a></th>
                    <th class="manage-column column-title column-primary sortable desc"><a>Status PagSeguro</a></th>
                    <th class="manage-column column-title" style="width: 5%">Ação</th>
                </tr>
            </tfoot>
            <tbody id="the-list">

            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url('/assets/js/jquery.js', PS_PLUGIN_DIR); ?>"></script>
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url('/assets/js/jquery.dataTables.min.js'); ?>"></script>
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url('/assets/js/modal.js', PS_PLUGIN_DIR); ?>"></script>
<script type="text/javascript" charset="utf8" src="<?php echo plugins_url('/assets/js/conciliation.js', PS_PLUGIN_DIR); ?>"></script>
