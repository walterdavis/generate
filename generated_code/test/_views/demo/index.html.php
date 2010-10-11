<?php
?>
<table>
	<tr>
		<th>actions</th>
		<th>id</th>
		<th>_private</th>
		<th>name</th>
		<th>rank</th>
		<th>serial_number</th>
		<th>quote</th>
		<th>updated_on</th>
		<th>added_at</th>
	</tr>
<?php
	foreach($objects as $object){
		print '	<tr>
		<td>
			' . ActionView::link_to("Show","show",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '
		</td>
		<td>' . $object->h('id') . '</td>
		<td>' . $object->h('_private') . '</td>
		<td>' . $object->h('name') . '</td>
		<td>' . $object->h('rank') . '</td>
		<td>' . $object->h('serial_number') . '</td>
		<td>' . $object->h('quote') . '</td>
		<td>' . $object->h('updated_on') . '</td>
		<td>' . $object->h('added_at') . '</td>
	</tr>
';
	}
?>
</table>
<p><?= ActionView::link_to("Create","create",$object) ?></p>
