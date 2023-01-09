<?php ob_start(); ?>

<h6><?php echo __('Number Resi:', 'sejoli-standalone-cod'); ?></h6>
<div class="shipping-number" style="font-size:26px;"><b><?php echo $trace_tracking_arveoli->cnote->cnote_no; ?></b></div>

<h6><?php echo __('Shipping Details:', 'sejoli-standalone-cod'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Courier:', 'sejoli-standalone-cod'); ?></th>
	<td>JNE - <?php echo $trace_tracking_arveoli->cnote->cnote_services_code; ?></td>
</tr>
<tr>
	<th><?php echo __('Total Price:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo sejolisa_price_format( $trace_tracking_arveoli->cnote->cnote_amount ); ?></td>
</tr>
<tr>
	<th><?php echo __('Weight:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->cnote_weight; ?> kg</td>
</tr>
<tr>
	<th><?php echo __('Send Date:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli->cnote->cnote_date ) ); ?></td>
</tr>
<tr>
	<th><?php echo __('From:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->detail[0]->cnote_shipper_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Shipper Address:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->detail[0]->cnote_shipper_addr1. ' ' .$trace_tracking_arveoli->detail[0]->cnote_shipper_addr2. ' ' .$trace_tracking_arveoli->detail[0]->cnote_shipper_city; ?></td>
</tr>
<tr>
	<th><?php echo __('To:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo  $trace_tracking_arveoli->detail[0]->cnote_receiver_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver Address:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->detail[0]->cnote_receiver_addr1. ' ' .$trace_tracking_arveoli->detail[0]->cnote_receiver_addr2. ' ' .$trace_tracking_arveoli->detail[0]->cnote_receiver_city; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->cnote_receiver_name.' - '.date_i18n( 'F d, Y H:i:s', strtotime($trace_tracking_arveoli->cnote->cnote_pod_date ) ); ?></td>
</tr>
<tr>
	<th><?php echo __('Last Status:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli->cnote->last_status; ?></td>
</tr>
</table>

<h6><?php echo __('Tracking History:', 'sejoli-standalone-cod'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Date', 'sejoli-standalone-cod'); ?></th>
	<th colspan="2"><?php echo __('Description', 'sejoli-standalone-cod'); ?></th>
</tr>
<?php
foreach ($trace_tracking_arveoli->history as $history) {
?>
<tr>
		<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $history->date ) ); ?></td>
		<td colspan="2"><?php echo $history->desc; ?></td>
	</tr>
<?php } ?>
</table>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	