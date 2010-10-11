<?php
?>
	<p><strong>title</strong><br />
		<?= $object->h('title') ?>
	</p>
	<p><strong>headline</strong><br />
		<?= $object->h('headline') ?>
	</p>
	<p><strong>quotation</strong></p>
	<?= ActionView::simple_format('quotation',$object) ?>
	<p><strong>fulltext</strong></p>
	<?= ActionView::simple_format('fulltext',$object) ?>
	<p><strong>thoughts</strong></p>
	<?= ActionView::simple_format('thoughts',$object) ?>
	<p><strong>theme</strong><br />
		<?= $object->h('theme') ?>
	</p>
	<p><strong>url</strong><br />
		<?= $object->h('url') ?>
	</p>
	<p><strong>images_id</strong><br />
		<?= $object->h('images_id') ?>
	</p>
	<p><strong>files_id</strong><br />
		<?= $object->h('files_id') ?>
	</p>
	<p><strong>titles_id</strong><br />
		<?= $object->h('titles_id') ?>
	</p>
	<p><strong>people_id</strong><br />
		<?= $object->h('people_id') ?>
	</p>
	<p><strong>weeks</strong><br />
		<?= $object->h('weeks') ?>
	</p>
	<p><strong>publish_on</strong><br />
		<?= $object->h('publish_on') ?>
	</p>
	<p><strong>added_at</strong><br />
		<?= $object->h('added_at') ?>
	</p>
	<?= '<p>' . ActionView::link_to("Index","index",$object) . ' | ' . ActionView::link_to("Edit","edit",$object) . '</p>' ?>
