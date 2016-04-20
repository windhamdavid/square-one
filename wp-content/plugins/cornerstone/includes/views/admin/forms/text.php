<?php

	$atts = cs_atts( array(
		'id'              => 'cs-control-' . $name,
		'name'            => $name,
		'value'           => $value,
		'class'           => 'tco-form-control tco-form-control-max',
		'type'            => 'text',
		'data-cs-control' => $type,
	) );

?>
<input <?php echo $atts; ?>>