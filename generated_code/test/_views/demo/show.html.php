<?php
?>
	<p><strong>_private</strong><br />
		<?= $object->h('_private') ?>
	</p>
	<p><strong>name</strong><br />
		<?= $object->h('name') ?>
	</p>
	<p><strong>rank</strong><br />
		<?= $object->h('rank') ?>
	</p>
	<p><strong>serial_number</strong><br />
		<?= $object->h('serial_number') ?>
	</p>
	<p><strong>quote</strong></p>
	<?= ActionView::simple_format('quote',$object) ?>
	<p><strong>updated_on</strong><br />
		<?= $object->h('updated_on') ?>
	</p>
	<p><strong>added_at</strong><br />
		<?= $object->h('added_at') ?>
	</p>
	<?= '<p>' . ActionView::link_to("Index","index",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '</p>' ?>
