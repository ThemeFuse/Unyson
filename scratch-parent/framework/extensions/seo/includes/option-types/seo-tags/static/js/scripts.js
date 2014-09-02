jQuery.fn.getCursorPosition = function getCursorPosition() {
	var node = this;
	if (node instanceof jQuery) {
		node = node[0];
	}
	//node.focus();
	/* without node.focus() IE will returns -1 when focus is not on node */
	if (node.selectionStart) {
		return node.selectionStart;
	}
	else if (!window.getSelection()) {
		return 0;
	}
	var c = "\001";
	var sel = document.selection.createRange();
	var dul = sel.duplicate();
	var len = 0;
	dul.moveToElementText(node);
	sel.text = c;
	len = (dul.text.indexOf(c));
	sel.moveStart('character', -1);
	sel.text = "";
	return len;
};

jQuery.fn.setCursorPosition = function (pos) {
	this.each(function (index, elem) {
		if (elem.setSelectionRange) {
			elem.setSelectionRange(pos, pos);
		} else if (elem.createTextRange) {
			var range = elem.createTextRange();
			range.collapse(true);
			range.moveEnd('character', pos);
			range.moveStart('character', pos);
			range.select();
		}
	});
	return this;
};

function getCurrentWord(node) {
	var value = node.val();
	var cursor = node.getCursorPosition();

	if (cursor > 1) {
		value = value.substring(0, cursor);
	}

	var word = value.match(/[^|\s|,|\-|\.|\!|\+]{1}[0-9|a-z|_|%]{1,}$/i);

	if (word == null) {
		return word;
	}

	var wordObject = {
		value: word[0],
		position: cursor - word[0].length,
		length: word[0].length
	};

	return wordObject;
}

jQuery(document).ready(function () {
	fwEvents.on('fw:options:init', function (data) {
		data.$elements.find('.fw-option-type-seo-tags').each(function () {
			var $this = jQuery(this);
			$this.autocomplete({
				source: fw_ext_seo_tags,
				minLength: 2,
				delay: 100,
				search: function (event, ui) {
					if ($this.attr('data-autocomplete-skip-next')) {
						$this.removeAttr('data-autocomplete-skip-next');
						return true;
					}

					$this.attr('data-autocomplete-skip-next', 'ok');

					var word = getCurrentWord($this);
					if (word == null) {
						return false
					}

					$this.cursorPosition = word.position;
					$this.wordLength = word.length;
					$this.autocomplete("search", word.value);

					return false;
				},
				focus: function (event, ui) {
					var tag = ui.item.value;
					var value = $this.val();
					$this.wordToReaplce = [value.slice(0, $this.cursorPosition), tag, value.slice($this.cursorPosition + $this.wordLength)].join('');
					return false;
				},
				select: function (event, ui) {
					$this.val($this.wordToReaplce);
					$this.setCursorPosition($this.cursorPosition + ui.item.value.length);
					return false;
				}
			})
		});
	});
});