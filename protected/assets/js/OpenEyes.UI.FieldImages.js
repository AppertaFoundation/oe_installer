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

(function(exports, Util, EventEmitter) {

	'use strict';
	/**
	 * FieldImages constructor.
	 * @constructor
	 * @name OpenEyes.UI.FieldImages
	 * @memberOf OpenEyes.UI
	 * @extends OpenEyes.Util.EventEmitter
	 * @example
	 * var fieldImages = new OpenEyes.UI.FieldImages({
	 *	images: OpenEyes.Util.FieldImagesList,
	 *	idToImages: array('ElementHTML_id' => OpenEyes.Util.FieldImagesList.property)
	 * });
	 */
	function FieldImages(options) {

		EventEmitter.call(this);

		this.options = $.extend(true, {}, FieldImages._defaultOptions, options);
		//this.create();
		//this.bindEvents();
	}

	Util.inherits(EventEmitter, FieldImages);

	/**
	 * The default dialog options. Custom options will be merged with these.
	 * @name OpenEyes.UI.FieldImages#_defaultOptions
     * @property {string} [title=null] - Dialog title
     * @property {array} [images=null] - Images available for fields
	 * @property {array} [idToImages=null] - Html ID to images relation
	 */
    FieldImages._defaultOptions = {
        title: "Field Images",
        images: null,
        idToImages:null
	};

	/**
	 * Creates and stores the dialog container, and creates a new jQuery UI
	 * instance on the container.
	 * @name OpenEyes.UI.FieldImages#create
	 * @method
	 * @private
	 */
    FieldImages.prototype.createDiag = function(fieldElId) {
        console.log("Creating diag for :" + fieldElId );
        console.log( "Getting vals " + JSON.stringify(this.options.idToImages[fieldElId ]) );
        console.log( "Getting vals selects " + JSON.stringify(this.options.idToImages[fieldElId ]['selects']) );

        var dialog = new OpenEyes.UI.Dialog({
            title: this.options.title,
            content: this.createImagesDiv(this.options.idToImages[fieldElId ])
        });
        dialog.open();
	};


    /**
     * Creates the Images container
     * @name OpenEyes.UI.FieldImages#createImagesDiv
     * @method
     * @private
     */
    FieldImages.prototype.createImagesDiv = function(fieldElId) {
        console.log("In create images div:" + JSON.stringify( fieldElId ) );
        //return JSON.stringify(dropDown) + " " + $(dropDown).id
        var wrapper = jQuery('<div/>', {
            class:  "fieldsWrapper"
        });
        for(var sval in fieldElId['selects']){
            console.log("In create images div, processing select :" + JSON.stringify( sval ) + " <- " + fieldElId['id']);
            var imgPath = null;
            if(fieldElId['id'] in this.options.images){
                console.log("In create images div fieldID:" + fieldElId['id'] );
                if(sval in this.options.images[fieldElId['id']]){

                    imgPath = this.options.images[fieldElId['id']][sval];
                    console.log("In create images div fieldID -> imgPath :" + imgPath );
                }
            }
            var el = jQuery('<div/>', {
                style: 'width:200px;height:129px'
            });
            if(imgPath){
                $(el).css("background-image", "url("+ imgPath + ")");
            }
            else{
                $(el).css("background-color", "#999");
            }
            $(el).appendTo(wrapper);
        }
        //return this.options.idToImages +   " " + JSON.stringify(selectVals);
        return wrapper;
    };

    /**
     * Sets fields  buttons in dom
     * @name OpenEyes.UI.FieldImages#setFieldButtons
     * @method
     * @private
     */
    FieldImages.prototype.setFieldButtons = function() {
        for (var selectId in this.options.idToImages) {
            if($('#' + selectId)){
                this.options.idToImages[selectId]
                jQuery('<img/>', {
                    id: selectId + "_cog",
                    src: OE_core_asset_path + '/img/fieldImages/cog.png',
                    alt: 'Opens ' + selectId + ' field images'
                }).insertAfter( '#' + selectId );

                var this_ = this;

                $( '#' + selectId + "_cog").click( function() {
                    var sId = this.id.substr(0, (this.id.length - 4) );
                    console.log("SetFieldButtonsTrying to open diag" +  JSON.stringify(sId) );
                    this_.createDiag( sId);
                });
            }
            else{console.log("Dropdown " + selectId + " does not exist!");}
            //console.log("Dropdown " + selectId + " processed!");
        }
        console.log("Dropdowns " + JSON.stringify(this.options.idToImages) + " processed!");
        //return this.options.idToImages +   " " + JSON.stringify(this.options.images);
    };

	/**
	 * Add content to FieldImages dialog.
	 * @name OpenEyes.UI.FieldImages#setContent
	 * @method
	 * @public
	 */
    FieldImages.prototype.setContent = function(content) {
		this.content.html(content);
	};

	/**
	 * Binds common dialog event handlers.
	 * @name OpenEyes.UI.FieldImages#create
	 * @method
	 * @private
	 */
	FieldImages.prototype.bindEvents = function() {
		this.content.on({
			dialogclose: this.onFieldImagesClose.bind(this),
			dialogopen: this.onFieldImagesOpen.bind(this)
		});
	};

	/**
	 * Sets the fieldImages title.
	 * @name OpenEyes.UI.FieldImages#setTitle
	 * @method
	 * @public
	 */
	FieldImages.prototype.setIdToImages = function(idToImages) {
		this.instance.option('idToImages', idToImages);
	};

    /**
     * Sets the fieldImages title.
     * @name OpenEyes.UI.FieldImages#setTitle
     * @method
     * @public
     */
    FieldImages.prototype.setTitle = function(title) {
        this.instance.option('title', title);
    };

	/** Event handlers */

	/**
	 * Emit the 'open' event after the dialog has opened.
	 * @name OpenEyes.UI.FieldImages#onFieldImagesOpen
	 * @fires OpenEyes.UI.FieldImages#open
	 * @method
	 * @private
	 */
	FieldImages.prototype.onFieldImagesOpen = function() {
		/**
		 * Emitted after the dialog has opened.
		 *
		 * @event OpenEyes.UI.FieldImages#open
		 */
		this.emit('open');
	};

	/**
	 * Emit the 'close' event after the dialog has closed, and optionally destroy
	 * the dialog.
	 * @name OpenEyes.UI.FieldImages#onFieldImagesClose
	 * @fires OpenEyes.UI.FieldImages#close
	 * @method
	 * @private
	 */
	FieldImages.prototype.onFieldImagesClose = function() {
		/**
		 * Emitted after the dialog has closed.
		 *
		 * @event OpenEyes.UI.FieldImages#close
		 */
		this.emit('close');

		if (this.options.destroyOnClose) {
			this.destroy();
		}
	};
	exports.FieldImages = FieldImages;

}(OpenEyes.UI, OpenEyes.Util, OpenEyes.Util.EventEmitter));