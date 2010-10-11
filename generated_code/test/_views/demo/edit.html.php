<?php
?>
<form action="" method="post" accept-charset="utf-8">
	<p><input type="hidden" name="id" value="<?=$object->id?>" /></p>
	<p><label for="_private">_private</label><input type="hidden" name="_private" value="0" /><input type="checkbox" name="_private" value="1" id="_private" class="boolean" /></p>
	<p><label for="name">name</label><input type="text" name="name" value="<?=$object->h('name')?>" id="name" class="text"/></p>
	<p><label for="rank">rank</label><input type="text" name="rank" value="<?=$object->h('rank')?>" id="rank" class="text"/></p>
	<p><label for="serial_number">serial_number</label><input type="text" name="serial_number" value="<?=$object->h('serial_number')?>" id="serial_number" class="text"/></p>
	<p><label for="quote">quote</label><textarea name="quote" rows="8" cols="40"><?=$object->h('quote')?></textarea></p>
	<p><label for="updated_on">updated_on</label><input type="text" name="updated_on" value="<?=$object->h('updated_on')?>" id="updated_on" class="date"/></p>
	<p><label for="added_at">added_at</label><input type="text" name="added_at" value="<?=$object->h('added_at')?>" id="added_at" class="datetime"/></p>
	<p><label for="save">&nbsp;</label><input type="submit" name="save" value="Save" id="save" class="save" />&nbsp; <input type="submit" name="delete" value="Delete" id="delete" class="delete" /> | <?= ActionView::link_to("Index","index",$object)?></p>
</form>
