/**
 * OpenEyes
 *
 * (C) Moorfields Eye Hospital NHS Foundation Trust, 2008-2011
 * (C) OpenEyes Foundation, 2011-2012
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2008-2011, Moorfields Eye Hospital NHS Foundation Trust
 * @copyright Copyright (c) 2011-2012, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

$(document).ready(function() {
	$('body').append('<div class="printable" id="printable"></div>');
});

/* PDF Printing */
/**
 * This is only here temporarily for testing. Remaining printElement code needs removing.
 */
function printPDF(url) {
	$('#print_pdf_iframe').remove();
	var iframe = document.createElement('iframe');
	$(iframe).attr({
		id: 'print_pdf_iframe',
		src: url,
		style: 'display: none;'
	});
	document.body.appendChild(iframe);
	iframe.contentWindow.print();
}
/* PDF Printing */

function clearPrintContent() {
	$('#printable').empty();
}

function appendPrintContent(content) {
	$('#printable').append(content);
}

function printContent(csspath) {
	
	var css = [ { href: '/css/printcontent.css', media: 'all' } ];
	if (csspath) {
		css = [ { href: csspath + '/print.css', media: 'all' } ];
	}

	$('#printable').printElement({
		pageTitle : 'OpenEyes printout',
		//leaveOpen: true,
		//printMode: 'popup',
		printBodyOptions : {
			styleToAdd : 'width: auto !important; margin: 0.75em !important;',
			classNameToAdd : 'openeyesPrintout'
		},
		overrideElementCSS : css,
	});
}

function printUrl(url, data, csspath) {
	$.post(url, data, function(content) {
		$('#printable').html(content);
		printContent(csspath);
	});
}

/**
 * Chromium 'Ignoring too frequent calls to print().' work around. Is a wrapper
 * around the printContent() function.
 */
if (navigator.userAgent.toLowerCase().indexOf("chrome") > -1) {
	
	// Wrap private vars in a closure
	(function() {
		var realPrintFunc = window.printContent;
		var interval = 35000; // 35 secs
		var timeout_id = null;
		var nextAvailableTime = +new Date(); // when we can safely print again

		function runPrint(csspath) {
			realPrintFunc(csspath);
			timeout_id = null;
			nextAvailableTime += interval;
		}

		// Overwrite window.printContent function
		window.printContent = function(csspath) {
			var now = +new Date();

			if (now > nextAvailableTime) {
				// if the next available time is in the past, print now
				realPrintFunc(csspath);
				nextAvailableTime = now + interval;
			} else {
				if (timeout_id !== null) {
					// Skip if setTimeout has already been called (prevents user
					// from calling print multiple times)
					console.log('Skipping print as count down already started '
							+ (nextAvailableTime - now) / 1000
							+ 's left until next print');
					alert("New print request has been queued. "
							+ Math.floor((nextAvailableTime - now) / 1000)
							+ "secs until print.");
					return;
				} else {
					// print when next available
					timeout_id = setTimeout(function() { runPrint(csspath); }, nextAvailableTime - now);
					alert("Print request has been queued. "
							+ Math.floor((nextAvailableTime - now) / 1000)
							+ "secs until print.");
				}
			}
		}

	})();
}
