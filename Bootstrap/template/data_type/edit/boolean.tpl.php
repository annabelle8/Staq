<input type="radio" name="model[<?= $this->content->name ?>]" value="1" <?= ( $this->content->value( ) ) ? 'checked="checked"' : '' ?> />Yes
<input type="radio" name="model[<?= $this->content->name ?>]" value="0" <?= ( $this->content->value( ) ) ? '' : 'checked="checked"' ?> />No