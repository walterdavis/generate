<?php
?>
<table>
	<tr>
		<th>actions</th>
		<th>id</th>
		<th>title</th>
		<th>headline</th>
		<th>quotation</th>
		<th>fulltext</th>
		<th>thoughts</th>
		<th>theme</th>
		<th>url</th>
		<th>images_id</th>
		<th>files_id</th>
		<th>titles_id</th>
		<th>people_id</th>
		<th>weeks</th>
		<th>publish_on</th>
		<th>added_at</th>
	</tr>
<?php
	foreach($objects as $object){
		print '	<tr>
		<td>
			' . ActionView::link_to("Show","show",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '
		</td>
		<td>' . $object->h('id') . '</td>
		<td>' . $object->h('title') . '</td>
		<td>' . $object->h('headline') . '</td>
		<td>' . $object->h('quotation') . '</td>
		<td>' . $object->h('fulltext') . '</td>
		<td>' . $object->h('thoughts') . '</td>
		<td>' . $object->h('theme') . '</td>
		<td>' . $object->h('url') . '</td>
		<td>' . $object->h('images_id') . '</td>
		<td>' . $object->h('files_id') . '</td>
		<td>' . $object->h('titles_id') . '</td>
		<td>' . $object->h('people_id') . '</td>
		<td>' . $object->h('weeks') . '</td>
		<td>' . $object->h('publish_on') . '</td>
		<td>' . $object->h('added_at') . '</td>
	</tr>
';
	}
?>
</table>
<p><?= ActionView::link_to("Create","create",$object) ?></p>
