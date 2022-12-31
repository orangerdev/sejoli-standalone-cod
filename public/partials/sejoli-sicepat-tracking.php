<?php ob_start(); ?>

<h6><?php echo __('Number Resi:', 'sejoli-standalone-cod'); ?></h6>
<div class="shipping-number" style="font-size:26px;"><b><?php echo $shipping_number; ?></b></div>

<h6><?php echo __('Shipping Details:', 'sejoli-standalone-cod'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Courier:', 'sejoli-standalone-cod'); ?></th>
	<td>SICEPAT - <?php echo $trace_tracking_arveoli_sicepat->sicepat->result->service; ?></td>
</tr>
<tr>
	<th><?php echo __('Total Price:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo wc_price( $trace_tracking_arveoli_sicepat->sicepat->result->totalprice ); ?></td>
</tr>
<tr>
	<th><?php echo __('Weight:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->weight; ?> kg</td>
</tr>
<tr>
	<th><?php echo __('Send Date:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $trace_tracking_arveoli_sicepat->sicepat->result->send_date ) ); ?></td>
</tr>
<tr>
	<th><?php echo __('From:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->sender; ?></td>
</tr>
<tr>
	<th><?php echo __('Shipper Address:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->sender_address; ?></td>
</tr>
<tr>
	<th><?php echo __('To:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->receiver_name; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver Address:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->receiver_address; ?></td>
</tr>
<tr>
	<th><?php echo __('Receiver:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->POD_receiver.' - '.date_i18n( 'F d, Y H:i:s', strtotime($trace_tracking_arveoli_sicepat->sicepat->result->POD_receiver_time ) ); ?></td>';
</tr>
<tr>
	<th><?php echo __('Last Status:', 'sejoli-standalone-cod'); ?></th>
	<td><?php echo $trace_tracking_arveoli_sicepat->sicepat->result->last_status->status.' - '.$trace_tracking_arveoli_sicepatsicepat->result->last_status->receiver_name; ?></td>';
</tr>
</table>

<h6><?php echo __('Tracking History:', 'sejoli-standalone-cod'); ?></h6>
<table style="text-align: left;">
<tr>
	<th><?php echo __('Date', 'sejoli-standalone-cod'); ?></th>
	<th><?php echo __('Status', 'sejoli-standalone-cod'); ?></th>
	<th><?php echo __('Status', 'sejoli-standalone-cod'); ?></th>
</tr>
<?php
foreach ($trace_tracking_arveoli_sicepat->sicepat->result->track_history as $history) {
?>
<tr>
		<td><?php echo date_i18n( 'F d, Y H:i:s', strtotime( $history->date_time ) ); ?></td>
		<td><?php echo $history->status; ?></td>
		<td><?php echo (isset($history->city)) ? $history->city : '-'; ?></td>
	</tr>
<?php } ?>
</table>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	