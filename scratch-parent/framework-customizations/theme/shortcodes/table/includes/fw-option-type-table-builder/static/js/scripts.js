( function ($) {
	$(document).ready(function () {

		function FwTableBuilder($tableBuilder) {
			var $table = $tableBuilder.find('.fw-table'),
				lastRow = parseInt($tableBuilder.find('.fw-table-last-row').val()),
				lastCol = parseInt($tableBuilder.find('.fw-table-last-col').val()),
				worksheetSelector = '.fw-table-cell-worksheet:not(.fw-template-row .fw-table-cell, fw-table-cols-delete .fw-table-cell, .fw-table-col-delete)',
				$currentCell = false,
				isAllowedTabMove = true;

			var process = {
				initialize: function () {
					process.tableBuilderEvents();
				},

				onTabKeyUp: function (e) {
					var keyCode = e.keyCode || e.which;
					if (keyCode == 9) {
						isAllowedTabMove = true;
					}
				},

				onTabKeyDown: function (e) {
					var keyCode = e.keyCode || e.which;
					if (keyCode == 9) {
						if (isAllowedTabMove === true && $currentCell) {
							isAllowedTabMove = false;
							process.onTabPress(e);
						} else if ($currentCell) {
							isAllowedTabMove = false;
							e.stopPropagation();
							e.preventDefault();
						}
					}
				},

				onTabPress: function (e) {
					var $cells = $table.find(worksheetSelector),
						currentCellIndex = $cells.index($currentCell),
						order = e.shiftKey ? -1 : 1,
						$nextCell = $cells.filter(':eq(' + (currentCellIndex + order) + ')');

					if (!$nextCell.length) {
						$nextCell = order == 1 ? $cells.filter(':eq(0)') : $cells.filter(':last');
					}

					e.stopPropagation();
					e.preventDefault();
					$nextCell.trigger('click');
				},

				changeTableRowStyle: function () {
					var $select = $(this),
						newClass = $select.val(),
						classNames = $select.find('option').map(function () {
							return this.value
						}).get().join(" "),
						$selectCell = $select.parent(),
						$row = $selectCell.parent();

					$row.removeClass(classNames).addClass(newClass);
					$selectCell.removeClass(classNames).addClass(newClass);

					return false;
				},

				/**
				 * Generate string of class names (from select values), which need tobe removed, after add new class for specific cells
				 */
				changeTableColumnStyle: function () {
					var $select = $(this),
						newClass = $select.val(),
						classNames = $select.find('option').map(function () {
							return this.value
						}).get().join(" "),
						$cell = $select.parent(),
						colId = parseInt($cell.data('col')),
						$elements = $table.find('[data-col=' + colId + ']');

					$elements.removeClass(classNames).addClass(newClass);

					return false;
				},

				removeTableColumn: function () {
					var columns = $table.find('.fw-template-row .fw-table-cell');

					if (columns.length > 3) {
						var colId = parseInt($(this).data('col'));
						$table.find('.fw-table-cell[data-col=' + colId + ']').remove();
					}

					return false;
				},

				removeTableRow: function () {
					var $row = $(this).parent('.fw-table-row');

					if (false === $(this).hasClass('empty-cell') && false === $row.hasClass('fw-template-row') && $table.find('.fw-table-row').length > 4) {
						$row.remove();
					}

					return false;
				},

				addTableColumn: function () {
					var columns = $table.find('.fw-template-row .fw-table-cell');
					lastCol++;

					//max cols
					if (columns.length <= 6) {
						/**
						 * Clone worksheet (data cells) and insert it before last row's cell
						 */
						var $dropdownColCell = $table.find('.fw-table-row:eq(0) .fw-table-cell:eq(1)'),
							dropDownDefaultColValue = $dropdownColCell.find('select option:eq(0)').val(),
							$worksheetCellTemplate = $table.find('.fw-template-row .fw-table-cell:eq(1)'),
							$beforeDeleteRowCell = $table.find('.fw-table-row:not(.fw-table-row:eq(0), .fw-table-cols-delete) .fw-table-row-delete'),
							$insertedDropDownCell = $worksheetCellTemplate.clone().addClass(dropDownDefaultColValue).insertBefore($beforeDeleteRowCell);
						$insertedDropDownCell.attr('data-col', lastCol);
						$insertedDropDownCell.each(function () {
							if (false === $(this).parent().hasClass('fw-template-row')) {
								var rowId = $(this).parent().data('row'),
									$textarea = $(this).find('textarea');

								$textarea.attr('name', $textarea.attr('name').replace(/_template_key_row_/, rowId).replace(/_template_key_col_/, lastCol));
								$textarea.attr('id', $textarea.attr('id').replace(/_template_key_row_/, rowId).replace(/_template_key_col_/, lastCol));
							}

						});

						/**
						 * Clone first cell with select and insert it before last row's cell
						 */
						var $lastEmptyCellFirstRow = $table.find('.fw-table-row:eq(0) .fw-table-row-delete'),
							clone2 = $dropdownColCell.clone().insertBefore($lastEmptyCellFirstRow);
						clone2.attr('data-col', lastCol).find('select').val(dropDownDefaultColValue);
						clone2.find('select').attr('name', clone2.find('select').attr('name').replace(/\[\d+]$/, '[' + lastCol + ']')); //add column number to select
						clone2.find('select').attr('id', clone2.find('select').attr('id').replace(/\-\d+$/, '-' + lastCol));

						/**
						 * Clone last row (row which consists with remove cols buttons) and insert it before last row's cell
						 */
						var deleteCellTemplate = $table.find('.fw-table-cols-delete .fw-table-cell:eq(1)'),
							$lastEmptyCellLastRow = $table.find('.fw-table-cols-delete .fw-table-cell:last'),
							clone3 = deleteCellTemplate.clone().insertBefore($lastEmptyCellLastRow);
						clone3.attr('data-col', lastCol);

						/**
						 * set column default style
						 */
						process.changeTableColumnStyle.apply(clone2.find('select'));
					}

					return false;
				},

				addTableRow: function () {
					lastRow++;
					var $templateRow = $tableBuilder.find('.fw-template-row'),
						$insertedRow = $templateRow.clone().removeClass('fw-template-row').attr('data-row', lastRow).insertBefore($templateRow);


					/**
					 * replace textarea templates names & id's
					 */
					$insertedRow.each(function () {
						if (false === $(this).hasClass('fw-template-row')) {

							var $textareas = $(this).find('textarea');

							$textareas.each(function () {
								var colId = $(this).parent().data('col');

								$(this).attr('name', $(this).attr('name').replace(/_template_key_row_/, lastRow).replace(/_template_key_col_/, colId));
								$(this).attr('id', $(this).attr('id').replace(/_template_key_row_/, lastRow).replace(/_template_key_col_/, colId));
							});

						}
					});

					var $select = $insertedRow.find('select');
					$select.attr('name', $select.attr('name').replace(/_template_key_row_/, lastRow));
					$select.attr('id', $select.attr('id').replace(/_template_key_row_/, lastRow));

					return false;
				},

				changeContent: function () {
					var value = $(this).val();
					$(this).parent().find('.fw-table-cell-content').text(value);
				},

				openEditor: function (e) {
					e.stopPropagation();
					var $cell = $(this);
					process.closeEditor();

					if ($cell.find('textarea').length) {
						$cell.addClass('fw-cell-show-editor').find('textarea').focus();
					}
					$currentCell = $cell;
				},

				closeEditor: function () {
					if ($currentCell) {
						$currentCell.removeClass('fw-cell-show-editor');
						$currentCell = false;
					}
				},

				tableBuilderEvents: function () {
					$table.on('click', '.fw-table-col-delete:not(.empty-cell)', process.removeTableColumn);
					$table.on('click', '.fw-table-row-delete:not(.empty-cell)', process.removeTableRow);
					$table.on('click', worksheetSelector, process.openEditor);
					$table.on('change', '.fw-table-cell textarea', process.changeContent);
					$table.on('change', 'select.fw-table-builder-col-style', process.changeTableColumnStyle);
					$table.on('change', 'select.fw-table-builder-row-style', process.changeTableRowStyle);
					$table.on('keydown', process.onTabKeyDown);
					$table.on('keyup', process.onTabKeyUp);
					$table.on('click', '.fw-table-add-column', process.addTableColumn);
					$table.on('click', '.fw-table-add-row', process.addTableRow);
					$(document).on('click', ':not(.fw-table-cell)', process.closeEditor);
				}

			};

			process.initialize();

		};

		fwEvents.on('fw:options:init', function (data) {
			data.$elements.find('.fw-option-type-table-builder:not(.fw-option-initialized)').each(function () {
				new FwTableBuilder($(this));
			}).addClass('fw-option-initialized');
		});
	});
}(jQuery));
