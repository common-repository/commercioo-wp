jQuery(document).ready(function ($) {
	/**
	 * Iterate all wpeditor classes
	 * This will init the editor and also the function
	 */
	$.each($('.comm-customizer-wpeditor'), function (i, editor) {
		var editor_id = $(editor).attr('id');
		var original_id = $(editor).attr('original-id');
			original_id = comm_escape_specials_chars_on_id(original_id);
		
		wp.editor.initialize(
			editor_id, {
				tinymce: {
					wpautop: true,
					plugins: 'charmap colorpicker compat3x directionality fullscreen hr image lists media paste tabfocus textcolor wordpress wpautoresize wpdialogs wpeditimage wpemoji wpgallery wplink wptextpattern wpview',
					toolbar1: 'bold bullist aligncenter | link unlink | wp_adv fullscreen',
					toolbar2: 'formatselect alignjustify forecolor | pastetext removeformat charmap | outdent indent | undo redo | blockquote hr wp_more | italic underline strikethrough | numlist alignleft alignright | wp_help'
				},
				quicktags: false,
				mediaButtons: false,
			}
		);
		
		/**
		 * Get the tinyMCE / editor's value 
		 * And then apply it on the original textarea
		 * Working with tinyMCE v4
		 */
		setTimeout(function () {
			var editor = tinyMCE.get(editor_id);

			if (editor) {
				editor.on('Change', function (e) {
					comm_set_original_content(editor, original_id);
				});

				editor.on('KeyUp', function (e) {
					comm_set_original_content(editor, original_id);
				});

				editor.on('Paste', function (e) {
					comm_set_original_content(editor, original_id);
				});
			}
		}, 1);		
	});	

	/**
	 * The javascript is not accept these specials chars for element selector
	 * So we must escape it before use it on any selector
	 */
	function comm_escape_specials_chars_on_id(str) {
		return str.replace(/(:|\.|\[|\]|,|=|@)/g, "\\$1");
	}

	/**
	 * Transfer the content from the dummy text area ro the origina's
	 */
	function comm_set_original_content(editor, original_id) {
		var content = editor.getContent();
		$('#' + original_id).val(content).trigger('change');
	}
});