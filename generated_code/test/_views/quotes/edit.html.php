<?php
?>
<form action="" method="post" accept-charset="utf-8">
	<p><input type="hidden" name="id" value="<?=$object->id?>" /></p>
	<p><label for="title">title</label><input type="text" name="title" value="<?=$object->h('title')?>" id="title" class="text"/></p>
	<p><label for="headline">headline</label><input type="text" name="headline" value="<?=$object->h('headline')?>" id="headline" class="text"/></p>
	<p><label for="quotation">quotation</label><textarea name="quotation" rows="8" cols="40"><?=$object->h('quotation')?></textarea></p>
	<p><label for="fulltext">fulltext</label><textarea name="fulltext" rows="8" cols="40"><?=$object->h('fulltext')?></textarea></p>
	<p><label for="thoughts">thoughts</label><textarea name="thoughts" rows="8" cols="40"><?=$object->h('thoughts')?></textarea></p>
	<p><label for="theme">theme</label><input type="text" name="theme" value="<?=$object->h('theme')?>" id="theme" class="text"/></p>
	<p><label for="url">url</label><input type="text" name="url" value="<?=$object->h('url')?>" id="url" class="text"/></p>
	<p><label for="images_id">images_id</label><input type="text" name="images_id" value="<?=$object->h('images_id')?>" id="images_id" class="integer"/></p>
	<p><label for="files_id">files_id</label><input type="text" name="files_id" value="<?=$object->h('files_id')?>" id="files_id" class="integer"/></p>
	<p><label for="titles_id">titles_id</label><input type="text" name="titles_id" value="<?=$object->h('titles_id')?>" id="titles_id" class="integer"/></p>
	<p><label for="people_id">people_id</label><input type="text" name="people_id" value="<?=$object->h('people_id')?>" id="people_id" class="integer"/></p>
	<p><label for="weeks">weeks</label><input type="text" name="weeks" value="<?=$object->h('weeks')?>" id="weeks" class="text"/></p>
	<p><label for="publish_on">publish_on</label><input type="text" name="publish_on" value="<?=$object->h('publish_on')?>" id="publish_on" class="date"/></p>
	<p><label for="added_at">added_at</label><input type="text" name="added_at" value="<?=$object->h('added_at')?>" id="added_at" class="datetime"/></p>
	<p><label for="save">&nbsp;</label><input type="submit" name="save" value="Save" id="save" class="save" />&nbsp; <input type="submit" name="delete" value="Delete" id="delete" class="delete" /> | <?= ActionView::link_to("Index","index",$object)?></p>
</form>
