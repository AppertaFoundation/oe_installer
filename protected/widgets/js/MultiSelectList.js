/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2013
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2013, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

$(document).ready(function() {

	// Prevent the events from being bound multiple times.
	if ($(this).data('multi-select-events')) {
		return;
	}
	$(this).data('multi-select-events', true);

	$(this).on('change', 'select.MultiSelectList', function() {

		var select = $(this);
		var selected = select.children('option:selected');

		if (selected.val().length >0) {

			var container = select.closest('.multi-select');
			var selections = container.find('.multi-select-selections');
			var inputField = container.find('.multi-select-list-name');
			var fieldName = inputField.attr('name').match(/\[MultiSelectList_(.*?)\]$/)[1];

			var attrs = {};
			$(selected[0].attributes).each(function() {
				attrs[this.nodeName] = this.nodeValue;
			});

			var inp_str = '<input type="hidden" name="'+fieldName+'[]"';
			for (var key in attrs) {
				inp_str += ' ' + key + '="' + attrs[key] + '"';
			}
			inp_str += ' />';

			var input = $(inp_str);

			var remove = $('<a />', {
				'href': '#',
				'class': 'MultiSelectRemove remove-one '+selected.val(),
				'text': 'Remove',
				'data-name': fieldName+'[]',
				'data-text': selected.text()
			});

			var item = $('<li>'+selected.text()+'</li>');
			item.append(remove);
			item.append(input);

			selections
			.append(item)
			.removeClass('hide');

			selected.remove();
			select.val('');
		}

		select.trigger('MultiSelectChanged');
		return false;
	});

	$(this).on('click', 'a.MultiSelectRemove', 'click',function(e) {
		e.preventDefault();
		var anchor = $(this);
		var container = anchor.closest('.multi-select');
		var selections = container.find('.multi-select-selections');
		var input = anchor.closest('li').find('input');

		var attrs = {};
		$(input[0].attributes).each(function() {
			if (this.nodeName != 'type' && this.nodeName != 'name') {
				attrs[this.nodeName] = this.nodeValue;
			}
		});

		var text = anchor.data('text');
		var select = container.find('select');

		var attr_str = '';
		for (var key in attrs) {
			attr_str += ' ' + key + '="' + attrs[key] + '"';
		}

		select.append('<option' + attr_str + '>'+text+'</option>');
		sort_selectbox(select);

		anchor.closest('li').remove();
		input.remove();

		if (!selections.children().length) {
			selections.addClass('hide');
		}

		select.trigger('MultiSelectChanged');

		return false;
	});
});
