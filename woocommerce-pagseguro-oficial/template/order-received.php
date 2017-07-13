<section class="ps-wrap checkout-success">
    <h1 class="title-detail">Detalhes do Pedido</h1>
    <?php
        $order = wc_get_order($_REQUEST['order_id']);
        $order_number = $order->get_order_number();
        //Products Info
        $order_items = $order->get_items();

        //Data
        $date_request = new DateTime($order->order_date);

        //Links to buttons
        $public_view_order_url = esc_url( $order->get_view_order_url() );
        $shop_url = get_permalink( get_option( 'woocommerce_shop_page_id' ) );
    ?>
    <div class="order-info">
        <h2 class="title text-center">Seu pedido foi efetuado com sucesso</h2>

        <p class="item">
            <span class="info-label">
                Data e Hora
            </span>
            <?php echo date_format($date_request, 'd/m/Y H:i:s'); ?>
        </p>
        <p class="item">
            <span class="info-label">
                O número do seu pedido é <a href="<?= $public_view_order_url ?>">#<?= $order_number ?></a>
            </span>
        </p>
        <table>
            <thead>
                <th>
                   Produto
                </th>
                <th>
                    Valor
                </th>
            </thead>
            <tbody>
            <?php
                foreach ($order_items as $item) {
                    $product_name = $item['name'];
            ?>
             <tr>
                 <td>
                     <?= $item['name'] ?>
                 </td>
                 <td>
                     R$ <?= $item['line_total'] ?>
                 </td>
             </tr>
            <?php
                }
            ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>
                        Total
                    </th>
                    <td class="total-price">
                        R$ <?= $order->get_total()?>
                    </td>
                </tr>

            </tfoot>
        </table>
    </div>
    <?php if ($_REQUEST['link_boleto']): ?>
        <div class="text-center">
            <p>Se a página de impressão do boleto não abriu, clique no link abaixo</p>
            <a class="btn-action-order -center" target="_blank" href="<?= $_REQUEST['link_boleto'];?>">
                Imprimir boleto
            </a>
        </div>
    <?php endif; ?>
    <?php if ($_REQUEST['link_debit']): ?>
        <div class="text-center">
            <p>Se a tela de pagamento não abriu clique no link abaixo</p>
            <a class="btn-action-order -center" target="_blank" href="<?= $_REQUEST['link_debit'];?>">
                Ir para o site do banco
            </a>
        </div>
    <?php endif; ?>

	<?php if ($_REQUEST['checkout_type'] == 'boleto'): ?>
        <div class="text-center">
            <a class="btn-action-order -center" target="_blank" href="<?= $_REQUEST['payment_link'];?>">
                Imprimir boleto
            </a>
        </div>
	<?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'debit'): ?>
        <div class="text-center">
            <a class="btn-action-order" target="_blank" href="<?= $_REQUEST['payment_link'];?>">
                Ir para o site do banco
            </a>
        </div>
    <?php endif; ?>

    <p class="text-or">Ou</p>
    <div class="action-bar clearfix">
        <a class="btn-action-order" href="<?= $public_view_order_url?>">
            Detalhes do pedido
        </a>
        <a class="btn-action-order" href="<?= $shop_url?>">
            Continue comprando
        </a>
    </div><!-- /.actions--bar -->
</section>