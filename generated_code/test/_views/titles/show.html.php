<?php
?>
	<p><strong>name</strong><br />
		<?= $object->h('name') ?>
	</p>
	<p><strong>description</strong></p>
	<?= ActionView::simple_format('description',$object) ?>
	<?= '<p>' . ActionView::link_to("Index","index",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '</p>' ?>
