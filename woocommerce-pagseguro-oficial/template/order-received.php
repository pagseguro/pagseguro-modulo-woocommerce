<section class="ps-wrap checkout-success">
    <h1 class="title-detail">Detalhes do Pedido</h1>
    <?php
        $order = wc_get_order($_REQUEST['order_id']);
        print_r($order);
        $public_view_order_url = esc_url( $order->get_view_order_url() );
        $shop_url = get_permalink( get_option( 'woocommerce_shop_page_id' ) );
    ?>
    <div class="order-info">
        <h2 class="title text-center">Seu pedido foi efetuado com sucesso</h2>
        <p class="item">
            <span class="info-label">Data e Hora:</span>
            <span class="info"><?= $order->order_date ?></span>
        </p>
    </div>
    <?php if ($_REQUEST['checkout_type'] == 'boleto'): ?>
        <div class="text-center">
            <a class="btn-action-order -center" target="_blank" href="<?= $_REQUEST['payment_link'];?>">
                Imprimir boleto
            </a>
        </div>
    <?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'debit'): ?>
        <div class="text-center">
            <a class="btn-action" target="_blank" href="<?= $_REQUEST['payment_link'];?>">
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