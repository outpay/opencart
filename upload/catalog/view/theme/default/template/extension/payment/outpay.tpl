<form id="outpay-form" action="<?php echo $checkout_url; ?>" method="get" target="_blank">
    <div class="buttons">
      <div class="pull-right">
        <input type="submit" <?php echo ($checkout_url) ? '' : 'disabled'; ?> value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" target="_blank"/>
      </div>
    </div>
</form>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
    $.ajax({
        type: 'get',
        url: 'index.php?route=extension/payment/outpay/confirm',
        cache: false,
        beforeSend: function() {
            $('#button-confirm').button('loading');
        },
        complete: function() {
            $('#button-confirm').button('reset');
        },
        success: function(data) {
            location = '<?php echo $continue; ?>'
        }
    });
});
//--></script>