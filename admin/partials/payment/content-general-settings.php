<?php
global $comm_options;
if(has_action("comm_content_general_payment_setting")):
?>
<div class="tab-pane active" id="general-payment" role="tabpanel">
    <form class="needs-validation" novalidate>
        <?php do_action("comm_content_general_payment_setting"); ?>
        <div class="col-md-7 mt-4 mb-4 ml-3 c-line-dash-settings"></div>
        <div class="col-md-6 set-margin-bottom-20 ms-4">
            <button type="submit"
                    class="btn btn-primary c-save-settings"><?php esc_html_e('Save', 'commercioo') ?></button>
            <input type="hidden" name="comm_key" value="gateways">
            <input type="hidden" name="comm_sub_key" value="gateways_general_settings">
        </div>
    </form>
</div>
<?php endif; ?>
