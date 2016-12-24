<section class="checkout-success">
    <h1 class="title-detail">Detalhes do Pedido</h1>
    <h2 class="title-order">Muito obrigado pela sua compra!!</h2>
    <?php
        $order = wc_get_order($_REQUEST['order_id']);
        print_r($order);
        $public_view_order_url = esc_url( $order->get_view_order_url() );
        $shop_url = get_site_url();
    ?>
    <?php if ($_REQUEST['checkout_type'] == 'boleto'): ?>
        <div class="actions-toolbar">
            <a class="action-order" href="<?= $public_view_order_url?>">
                Ver pedido gerado
            </a>
            <a class="action-order" href="<?= $shop_url?>">
                Continue comprando
            </a>
            <p class="text-or">Ou</p>
            <a class="ps-button " target="_blank" href="<?= $_REQUEST['payment_link'];?>">
                Imprimir boleto
            </a>
        </div>
    <?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'debit'): ?>
        <div class="actions-toolbar">
            <a class="ps-button" target="_blank" href="<?= $_REQUEST['payment_link'];?>">
                Ir para o site do banco
            </a>
            <p class="text-or">Ou</p>
            <a class="action-order" href="<?= $shop_url?>">
                Continue comprando
            </a>
        </div>
    <?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'credit-card'): ?>
        <div class="actions-toolbar">
            <a class="action-order" href="<?= $public_view_order_url?>">
                Ver pedido gerado
            </a>
            <p class="text-or">Ou</p>
            <a class="action-order" href="<?= $shop_url?>">
                Continue comprando
            </a>
        </div>
    <?php endif; ?>
</section>