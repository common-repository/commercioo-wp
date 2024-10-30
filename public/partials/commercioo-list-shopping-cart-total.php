<tfoot>
<tr class="set-border-bottom">
    <th scope="col" class="shopping-head">
        <?php esc_html_e('SUBTOTAL', 'commercioo') ?> :
        <span class="subtotal-right-cart comm-subtotal-price set-font-weight-normal">
	  <?php echo esc_html(Commercioo\Helper::formatted_currency(Commercioo\Cart::get_subtotal())); ?>
	    </span>
    </th>
</tr>
<?php do_action("commercioo/cart/display/fees");?>
<tr class="set-border-bottom">
    <th scope="col" class="shopping-head">
        <?php esc_html_e('TOTAL', 'commercioo') ?> :
        <span class="subtotal-right-cart comm-grandtotal-price"><?php echo esc_html(Commercioo\Helper::formatted_currency(Commercioo\Cart::get_total())); ?>
        </span>
    </th>
</tr>
</tfoot>