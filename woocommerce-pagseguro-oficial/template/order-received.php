<div class="checkout-success">

    <p>Numero da Ordem</p>

    <p>'We\'ll email you an order confirmation with details and tracking info.'</p>

    <?php var_dump($_REQUEST); ?>

    <?php if ($_REQUEST['checkout_type'] == 'boleto'): ?>
    <div class="actions-toolbar">
        <div class="primary">
            <a class="action primary continue" target="_blank" href="<?= $_REQUEST['payment_link'];?>"><span>Imprimir boleto</span></a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'debit'): ?>
    <div class="actions-toolbar">
        <div class="primary">
            <a class="action primary continue" target="_blank" href="<?= $_REQUEST['payment_link'];?>"><span>Ir para o site do banco</span></a>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($_REQUEST['checkout_type'] == 'credit-card'): ?>
    <div class="actions-toolbar">
        <div class="primary">
            <a class="action primary continue" href=""><span>Continue comprando</span></a>
        </div>
    </div>
    <?php endif; ?>
</div>