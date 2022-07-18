<?php ob_start(); ?>

<form id="shipment-tracking-form" method="POST" action="">
	<label for="shipment-number"><?php echo __('Shipment Expedition', 'sejoli-standalone-cod'); ?></label>
	<select name="shipment-expedition" id="shipment-expedition">
  		<option value="jne"><?php _e('JNE', 'sejoli-standalone-cod') ?></option>
  		<option value="sicepat"><?php _e('SiCepat', 'sejoli-standalone-cod') ?></option>
  	</select>
  	<label for="shipment-number"><?php echo __('Shipment Number', 'sejoli-standalone-cod'); ?></label>
  	<input type="text" id="shipment-number" name="shipment-number" value="">
  	<br>
  	<input type="submit" name="submit-tracking" value="Search" >
</form>';
 
<div id="shipment-history"></div>

<?php
	$html = ob_get_contents();
	ob_end_clean();
?>	