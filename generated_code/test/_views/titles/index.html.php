<?php
?>
<table>
	<tr>
		<th>actions</th>
		<th>id</th>
		<th>name</th>
		<th>description</th>
	</tr>
<?php
	foreach($objects as $object){
		print '	<tr>
		<td>
			' . ActionView::link_to("Show","show",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '
		</td>
		<td>' . $object->h('id') . '</td>
		<td>' . $object->h('name') . '</td>
		<td>' . $object->h('description') . '</td>
	</tr>
';
	}
?>
</table>
<p><?= ActionView::link_to("Create","create",$object) ?></p>
