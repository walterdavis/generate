<?php
?>
<form action="" method="post" accept-charset="utf-8">
	<p><label for="name">name</label><input type="text" name="name" value="<?=$object->h('name')?>" id="name" class="text"/></p>
	<p><label for="description">description</label><textarea name="description" rows="8" cols="40"><?=$object->h('description')?></textarea></p>
	<p><label for="save">&nbsp;</label><input type="submit" name="save" value="Save" id="save" class="save" /> | <?= ActionView::link_to("Cancel","index",$object)?></p>
</form>
