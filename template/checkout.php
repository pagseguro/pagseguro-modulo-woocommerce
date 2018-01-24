<?php
/**
 ************************************************************************
 * Copyright [2016] [PagSeguro Internet Ltda.]
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 ************************************************************************
 */

$user_data = get_user_meta(get_current_user_id(), '_pagseguro_data', true);

if ($user_data) {
    $response = current($user_data);
    $js = $user_data['js'];
    delete_user_meta(get_current_user_id(), '_pagseguro_data');
}
?>

<script type="text/javascript" src="<?= $js ?>"></script>
<script type="text/javascript">
    PagSeguroLightbox(
        '<?= $response->getCode() ?>',
        {
            success: function () {
                window.location.href = "<?php echo sprintf('%s/%s', get_site_url(),
                    'index.php/checkout/order-received');?>";

            },
            abort: function (error) {
                //todo error page
            }
        }
    );
</script>

<h2>Finalizando sua compra com PagSeguro</h2>

<div>Sua compra está em processo de finalização, aguarde um instante.</div>
