<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-outpay" data-toggle="tooltip" title="<?php echo $button_save; ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>" class="btn btn-default"><i class="fa fa-reply"></i></a></div>
      <h1><?php echo $heading_title; ?></h1>
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb) { ?>
        <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
        <?php } ?>
      </ul>
    </div>
  </div>
  <div class="container-fluid">
    <?php if ($error_warning) { ?>
    <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_warning; ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>
    <div class="panel panel-default">
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
      </div>
      <div class="panel-body">
        <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-outpay" class="form-horizontal">

          <!-- Token -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_token; ?>"><?php echo $entry_token; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="outpay_token" value="<?php echo $outpay_token; ?>" placeholder="<?php echo $entry_token; ?>" id="input-total" class="form-control" />
            </div>
          </div>

          <!-- Total -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-total"><span data-toggle="tooltip" title="<?php echo $help_total; ?>"><?php echo $entry_total; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="outpay_total" value="<?php echo $outpay_total; ?>" placeholder="<?php echo $entry_total; ?>" id="input-total" class="form-control" />
            </div>
          </div>

          <!-- Statuses -->
          <?php foreach($outpay_statuses as $outpay_order_status){ ?>
            <div class="form-group">
              <label class="col-sm-2 control-label" for="input-order-status-<?php echo $outpay_order_status; ?>"><?php echo ${'entry_order_status_'.$outpay_order_status}; ?></label>
              <div class="col-sm-10">
                <select name="outpay_order_status_<?php echo $outpay_order_status; ?>" id="input-order-status-<?php echo $outpay_order_status; ?>" class="form-control">
                  <?php foreach ($order_statuses as $order_status) { ?>
                  <?php if ($order_status['order_status_id'] == ${'outpay_order_status_'.$outpay_order_status}) { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>" selected="selected"><?php echo $order_status['name']; ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $order_status['order_status_id']; ?>"><?php echo $order_status['name']; ?></option>
                  <?php } ?>
                  <?php } ?>
                </select>
              </div>
            </div>
          <?php } ?>

          <!-- Notify -->
          <div class="form-group required">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_notify ?>"><?php echo $entry_notify ?></span></label>
            <div class="col-sm-10">
              <select name="outpay_notify" class="form-control">
                <?php if ($outpay_notify) { ?>
                <option value="1" selected><?php echo $text_yes ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_yes ?></option>
                <?php } ?>
                
                <?php if (!$outpay_notify) { ?>
                <option value="0" selected><?php echo $text_no ?></option>
                <?php } else { ?>
                <option value="0"><?php echo $text_no ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <!-- Custom Field (Number) -->
          <div class="form-group required">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_address_number ?>"><?php echo $entry_address_number ?></span></label>
            <div class="col-sm-10">
              <span class="input-group">
                <select name="outpay_address_number" class="form-control">
                <?php foreach($custom_fields as $custom_field) { ?>
                <?php if ($outpay_address_number == $custom_field['custom_field_id']) { ?>
                <option value="<?php echo $custom_field['custom_field_id'] ?>" selected><?php echo $custom_field['name'] ?></option>
                <?php } else { ?>
                <option value="<?php echo $custom_field['custom_field_id'] ?>"><?php echo $custom_field['name'] ?></option>
                <?php } ?>
                <?php } ?>
                </select>
                
                <span class="input-group-btn">
                  <a href="<?php echo $link_custom_field ?>" class="btn btn-primary"><?php echo $text_custom_field ?></a>
                </span>
              </span>
            </div>
          </div>

          <!-- Custom Field (CPF | CNPJ) -->
          <div class="form-group required">
            <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_cpf ?>"><?php echo $entry_cpf ?></span></label>
            <div class="col-sm-10">
              <span class="input-group">
                <select name="outpay_cpf" class="form-control">
                <?php foreach($custom_fields as $custom_field) { ?>
                <?php if ($outpay_cpf == $custom_field['custom_field_id']) { ?>
                <option value="<?php echo $custom_field['custom_field_id'] ?>" selected><?php echo $custom_field['name'] ?></option>
                <?php } else { ?>
                <option value="<?php echo $custom_field['custom_field_id'] ?>"><?php echo $custom_field['name'] ?></option>
                <?php } ?>
                <?php } ?>
                </select>
                
                <span class="input-group-btn">
                  <a href="<?php echo $link_custom_field ?>" class="btn btn-primary"><?php echo $text_custom_field ?></a>
                </span>
              </span>
            </div>
          </div>
    
          <!-- Callback -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-callback"><span data-toggle="tooltip" title="<?php echo $help_callback; ?>"><?php echo $entry_callback; ?></span></label>
            <div class="col-sm-10">
              <input type="text" name="outpay_callback" value="<?php echo $outpay_callback; ?>" placeholder="https://www.MYSTORE.com.br/index.php?route=payment/outpay/callback" id="input-callback" class="form-control" />
            </div>
          </div>

          <!-- Geo Zone -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-geo-zone"><?php echo $entry_geo_zone; ?></label>
            <div class="col-sm-10">
              <select name="outpay_geo_zone_id" id="input-geo-zone" class="form-control">
                <option value="0"><?php echo $text_all_zones; ?></option>
                <?php foreach ($geo_zones as $geo_zone) { ?>
                <?php if ($geo_zone['geo_zone_id'] == $outpay_geo_zone_id) { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>" selected="selected"><?php echo $geo_zone['name']; ?></option>
                <?php } else { ?>
                <option value="<?php echo $geo_zone['geo_zone_id']; ?>"><?php echo $geo_zone['name']; ?></option>
                <?php } ?>
                <?php } ?>
              </select>
            </div>
          </div>

          <!-- Status -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-status"><?php echo $entry_status; ?></label>
            <div class="col-sm-10">
              <select name="outpay_status" id="input-status" class="form-control">
                <?php if ($outpay_status) { ?>
                <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                <option value="0"><?php echo $text_disabled; ?></option>
                <?php } else { ?>
                <option value="1"><?php echo $text_enabled; ?></option>
                <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                <?php } ?>
              </select>
            </div>
          </div>

          <!-- Sort Order -->
          <div class="form-group">
            <label class="col-sm-2 control-label" for="input-sort-order"><?php echo $entry_sort_order; ?></label>
            <div class="col-sm-10">
              <input type="text" name="outpay_sort_order" value="<?php echo $outpay_sort_order; ?>" placeholder="<?php echo $entry_sort_order; ?>" id="input-sort-order" class="form-control" />
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<?php echo $footer; ?>