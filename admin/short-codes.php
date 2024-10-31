<?php
global $shortcode_tags;
?>
<table class="widefat widefat-shortcodes" cellspacing="0" style="max-width: 50%;">
    <thead>
    <tr>

        <th id="columnname" class="manage-column column-columnname" scope="col">Description</th>
        <th id="columnname" class="manage-column column-columnname" scope="col">Shortcode</th>
    </tr>
    </thead>
    <tbody>
	<?php
	global $shortcode_tags;

	$index = 0;
	foreach ( $shortcode_tags as $code => $details ) {

		if ( strpos( $code, 'referrizer_' ) === false ) {
			continue;
		}

		printf(
			'<tr class="%s">
            <td class="column-columnname"><b>%s</b></br>%s</td>
            <td class="column-columnname"></br>[%s]</td>
        </tr>', $index % 2 !== 0 ? 'alternate' : 'normal', $details[0]->name, $details[0]->widget_options['description'], $code );
		$index ++;
	}
	?>
    </tbody>
</table>
