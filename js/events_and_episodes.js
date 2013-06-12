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

$(document).ready(function(){
	$collapsed = true;

	$('button.addEvent').click(function(e) {
		var subspecialty_id = $(this).attr('data-attr-subspecialty-id');
		var returnUrl = window.location.href.replace(/#$/,'')+'#addEvent';

		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/addNewEvent',
			'data': 'subspecialty_id='+subspecialty_id+'&patient_id='+OE_patient_id+'&returnUrl='+returnUrl+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(html) {
				if (html == "0") {
					alert("Sorry, you cannot add an event to this episode because you are not in any firms with the same subspecialty.");
				} else {
					$('#user_panel').before(html);
				}
			}
		});
	});

	$('button.cancelAddEvent').die('click').live('click',function(e) {
		$('#add-new-event-dialog').dialog('close');
		$('#add-new-event-dialog').parent().remove();
		e.preventDefault();
	});

	if (window.location.href.match(/#addEvent$/)) {
		$('button.addEvent[data-attr-subspecialty-id="'+OE_subspecialty_id+'"]').click();
	}

	$('button.addEpisode').click(function(e) {
		$.ajax({
			'type': 'POST',
			'url': baseUrl+'/patient/verifyAddNewEpisode?patient_id='+OE_patient_id+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
			'success': function(response) {
				if (response != '1') {
					alert("There is already an open episode for your firm's subspecialty.\n\nIf you wish to create a new episode in a different subspecialty please switch to a firm that has the subspecialty you want.");
				} else {
					$.ajax({
						'type': 'POST',
						'url': baseUrl+'/patient/addNewEpisode',
						'data': 'patient_id='+OE_patient_id+"&YII_CSRF_TOKEN="+YII_CSRF_TOKEN,
						'success': function(html) {
							$('#user_panel').before(html);
						}
					});
				}
			}
		});

		e.preventDefault();
	});

	$('label').die('click').live('click',function() {
		if ($(this).prev().is('input:radio')) {
			$(this).prev().click();
		}
	});

	$(this).undelegate('select.dropDownTextSelection','change').delegate('select.dropDownTextSelection','change',function() {
		if ($(this).val() != '') {
			var target = $('#' + $(this).attr('id').replace(/^dropDownTextSelection_/,''));
			var currentVal = target.val();
			
			if($(this).hasClass('delimited')) {
				var newText = $(this).val();
			} else {
				var newText = $(this).children('option:selected').text();
			}

			if (currentVal.length > 0 && !$(this).hasClass('delimited')) {
				if (newText.toUpperCase() == newText) {
					newText = ', ' + newText;
				} else {
					newText = ', ' + newText.charAt(0).toLowerCase() + newText.slice(1);
				}
			} else if (currentVal.length == 0 && $(this).hasClass('delimited')) {
				newText = newText.charAt(0).toUpperCase() + newText.slice(1)
			}
			
			target.val(currentVal + newText);
			target.trigger('autosize');

			$(this).val('');
		}
	});
	
});

function selectSort(a, b) {		 
		if (a.innerHTML == rootItem) {
				return -1;		
		}
		else if (b.innerHTML == rootItem) {
				return 1;  
		}				
		return (a.innerHTML > b.innerHTML) ? 1 : -1;
};

var rootItem = null;

function sort_selectbox(element) {
	rootItem = element.children('option:first').text();
	element.append(element.children('option').sort(selectSort));
}

function WidgetSlider() {if (this.init) this.init.apply(this, arguments); }

WidgetSlider.prototype = {
	init : function(params) {
		for (var i in params) {
			this[i] = params[i];
		}

		var thiz = this;

		$(document).ready(function() {
			thiz.bindElement();
		});
	},

	bindElement : function() {
		var thiz = this;

		$('#'+this.range_id).change(function() {
			thiz.handleChange($(this));
		});
	},

	handleChange : function(element) {
		var val = element.val();

		for (var value in this.remap) {
			if (val == value) {
				val = this.remap[value];
			}
		}

		if (this.null.length >0) {
			if (val.match(/\./)) {
				val = String(parseFloat(val) - 1);
			} else {
				val = String(parseInt(val) - 1);
			}
		}

		var min = $('#'+this.range_id).attr('min');

		if (min.match(/\./)) {
			min = parseFloat(min);
		} else {
			min = parseInt(min);
		}

		if (val < min && this.null.length >0) {
			val = this.null;
		} else {
			if (this.force_dp) {
				if (!val.match(/\./)) {
					val += '.';
					for (var i in this.force_dp) {
						val += '00';
					}
				} else {
					var dp = val.replace(/^.*?\./,'');

					while (dp.length < this.force_dp) {
						dp += '0';
						val += '0';
					}
					
					while (dp.length > this.force_dp) {
						dp = dp.replace(/.$/,'');
						val = val.replace(/.$/,'');
					}
				} 
			}

			if (this.prefix_positive && parseFloat(val) >0) {
				var val = this.prefix_positive + val;
			}
		}

		$('#'+this.range_id+'_value_span').text(val+this.append);
	}
}

function WidgetSliderTable() {if (this.init) this.init.apply(this, arguments); }

WidgetSliderTable.prototype = {
	init : function(params) {
		for (var i in params) {
			this[i] = params[i];
		}

		var thiz = this;

		$(document).ready(function() {
			thiz.bindElement();
		});
	},

	bindElement : function() {
		var thiz = this;

		$('#'+this.range_id).change(function() {
			thiz.handleChange($(this));
		});
	},

	handleChange : function(element) {
		var val = element.val();

		$('#'+this.range_id+'_value_span').text(this.data[val]);
	}
}
