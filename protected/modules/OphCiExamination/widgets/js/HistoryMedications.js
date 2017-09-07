/**
 * OpenEyes
 *
 * (C) OpenEyes Foundation, 2017
 * This file is part of OpenEyes.
 * OpenEyes is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * OpenEyes is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along with OpenEyes in a file titled COPYING. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package OpenEyes
 * @link http://www.openeyes.org.uk
 * @author OpenEyes <info@openeyes.org.uk>
 * @copyright Copyright (c) 2017, OpenEyes Foundation
 * @license http://www.gnu.org/licenses/gpl-3.0.html The GNU General Public License V3.0
 */

var OpenEyes = OpenEyes || {};

OpenEyes.OphCiExamination = OpenEyes.OphCiExamination || {};

(function(exports) {
  function HistoryMedicationsController(options) {
    this.options = $.extend(true, {}, HistoryMedicationsController._defaultOptions, options);
    this.$element = this.options.element;
    this.$table = this.$element.find('table');
    this.templateText = this.$element.find('.entry-template').text();
    this.drugsByRisk = {};
    this.initialiseFilters();
    this.initialiseTriggers();
    this.initialiseRisks();
  }

  HistoryMedicationsController._defaultOptions = {
    modelName: 'OEModule_OphCiExamination_models_HistoryMedications',
    element: undefined,
    addButtonSelector: '.add-entry',
    searchSource: '/medication/finddrug',
    routeOptionSource: '/medication/retrieveDrugRouteOptions',
    searchAsTypedPrefix: 'As typed: ',
    drugFieldSelector: 'input[name$="[drug_id]"]',
    medicationFieldSelector: 'input[name$="[medication_drug_id]"]',
    asTypedFieldSelector: 'input[name$="[medication_name]"]',
    medicationSearchSelector: 'input[name$="[medication_search]"]',
    drugSelectSelector: 'select[name$="[drug_select]"]',
    medicationNameSelector: '.medication-name',
    medicationDisplaySelector: '.medication-display',
    startDateButtonSelector: '.start-medication.enable',
    cancelStartDateButtonSelector: '.start-medication.cancel',
    stopDateFieldSelector: 'input[name$="[stop_date]"]',
    stopDateButtonSelector: '.stop-medication.enable',
    cancelStopDateButtonSelector: '.stop-medication.cancel',
    routeFieldSelector: 'select[name$="[route_id]"]',
    routeOptionWrapperSelector: '.admin-route-options'
  };

  /**
  * Sets up the filter controls on the table.
  */
  HistoryMedicationsController.prototype.initialiseFilters = function()
  {
    // if there aren't any stopped medications, then the filter is irrelevant
    if (!this.$table.find('tr.originally-stopped').length) {
      this.$element.find('.show-stopped').hide();
      this.$element.find('.hide-stopped').hide();
    } else {
      this.hideStopped();
    }
  };

  HistoryMedicationsController.prototype.initialiseTriggers = function()
  {
    var controller = this;

    // removal button for table entries
    controller.$table.on('click', '.button.remove', function(e) {
      e.preventDefault();
      $(e.target).parents('tr').remove();
    });

    // setup current table row behaviours
    controller.$table.find('tbody tr').each(function() {
      controller.initialiseRow($(this));
    });

    // adding entries
    controller.$element.on('click', controller.options.addButtonSelector, function(e) {
      e.preventDefault();
      controller.addEntry();
    });

    controller.$element.on('click', '.show-stopped', function(e) {
        e.preventDefault();
        controller.showStopped();
    });

    controller.$element.on('click', '.hide-stopped', function(e) {
        e.preventDefault();
        controller.hideStopped();
    });
  };

  /**
   * Parses the current medications and ensures the internal and core stores
   * of risks for drugs is up to date with the current state.
   */
  HistoryMedicationsController.prototype.initialiseRisks = function()
  {
      var self = this;
      var drugs = {};
      var med_drugs = {};

      // Drugs could be 'drugs' or 'medication drugs' so parse table rows accordingly
      self.$table.find('tbody tr').each(function() {
          var name = $(this).find(self.options.medicationNameSelector).text();
          var id = $(this).find(self.options.drugFieldSelector).val();
          if (id) {
              drugs[id] = name;
          } else {
              id = $(this).find(self.options.medicationFieldSelector).val();
              if (id) {
                  med_drugs[id] = name;
              }
          }
      });

      var complete = 0;
      function handleComplete() {
          if (++complete === 2) {
              self.updateCoreRisks();
          }
      }

      self.mapDrugsToExternalRisks('/OphCiExamination/Risks/forDrugIds', drugs, handleComplete);
      self.mapDrugsToExternalRisks('/OphCiExamination/Risks/forMedicationDrugIds', med_drugs, handleComplete);
  };

  /**
   * Simple abstraction to take ids for tagged models, and retrieve the related risks
   * and then store to the internal register drug name to risks
   * @param url
   * @param idsToNames
   * @param callback
   */
  HistoryMedicationsController.prototype.mapDrugsToExternalRisks = function(url, idsToNames, callback)
  {
      var self = this;
      $.getJSON(url, {'ids': Object.keys(idsToNames).join(',') }, function(data) {
          for (var drugId in data) {
              if (data.hasOwnProperty(drugId)) {
                  self.addDrugForRisks(idsToNames[drugId], data[drugId]);
              }
          }
          callback();
      });
  };

  HistoryMedicationsController.prototype.initialiseRow = function($row)
  {
      var controller = this;
      controller.initialiseSearch($row.find('input.search'));

      $row.on('change', controller.options.drugSelectSelector, function(e) {
          controller.selectMedication($(this).parents('td'), {
              value: $(this).val(),
              label: $(this).find('option:selected').text(),
              type: 'd' // only have pre-selected drugs available at the moment.
          })
      });

      $row.on('click', '.medication-rename', function(e) {
          e.preventDefault();
          controller.resetSearchRow($row, true);
      });

      $row.on('click', controller.options.startDateButtonSelector, function(e) {
        e.preventDefault();
        controller.showDate($row, 'start');
      });

    $row.on('click', controller.options.cancelStartDateButtonSelector, function(e) {
      e.preventDefault();
      controller.cancelDate($row, 'start');
    });

      $row.on('click', controller.options.stopDateButtonSelector, function(e) {
          e.preventDefault();
          controller.showDate($row, 'stop');
      });

      $row.on('click', controller.options.cancelStopDateButtonSelector, function(e) {
          e.preventDefault();
          controller.cancelDate($row, 'stop');
      });

      $row.on('change', controller.options.routeFieldSelector, function(e) {
          controller.updateRowRouteOptions($row);
      });

      $row.on('change', '.fuzzy-date select', function(e) {
          var $fuzzyFieldset = $(this).closest('fieldset');
          var date = controller.dateFromFuzzyFieldSet($fuzzyFieldset);
          $fuzzyFieldset.find('input[type="hidden"]').val(date);
      });

      if (!$row.find(controller.options.medicationNameSelector).text().length) {
        controller.resetSearchRow($row, true);
      }
  };

  HistoryMedicationsController.prototype.initialiseSearch = function($el)
  {
    var controller = this;
    if (!$el.data('search')) {
        $el.autocomplete({
            minLength: 3,
            delay: 300,
            source: function(request, response) {
                $.getJSON(controller.options.searchSource, {
                    term: request.term,
                    ajax: 'ajax'
                }, response);
            },
            focus: function (event, ui) {
                event.preventDefault();
                $el.val(controller.getItemDisplayValue(ui.item));
            },
            select: function (event, ui) {
                controller.searchSelect($el, event, ui);
            },
            response: function (event, ui) {
                ui.content.push({
                    value: $el.val(),
                    label: controller.options.searchAsTypedPrefix + $el.val(),
                    type: 't'
                });
            }
        });
    }
  };

  HistoryMedicationsController.prototype.getItemDisplayValue = function(item)
  {
      if (item.type == 't') {
          return item.label.replace(this.options.searchAsTypedPrefix, '');
      }
      return item.label;
  };

  HistoryMedicationsController.prototype.searchSelect = function($el, event, ui)
  {
    event.preventDefault();
    var $container = $el.parents('td');
    this.resetSearchRow($container, false);

    this.selectMedication($container, ui.item);
    // set the search text box to the full value chosen
    $el.val(this.getItemDisplayValue(ui.item));
  };

  HistoryMedicationsController.prototype.selectMedication = function($container, item)
  {
      var displayText = this.getItemDisplayValue(item);

      if (item.type == 't') {
          $container.find(this.options.asTypedFieldSelector).val(item.value);
      }
      else if (item.type == 'd') {
          $container.find(this.options.drugFieldSelector).val(item.value);
          this.loadDrugDefaults($container.parents('tr'), item);
      } else {
          $container.find(this.options.medicationFieldSelector).val(item.value);
      }
      $container.find(this.options.medicationNameSelector).text(displayText);

      $container.find(this.options.medicationDisplaySelector).show();
      $container.find(this.options.medicationSearchSelector).hide();
      $container.find(this.options.drugSelectSelector).hide();

      this.processRisks(item);
  };

  HistoryMedicationsController.prototype.loadDrugDefaults = function($row, item)
  {
      $.getJSON('/medication/drugdefaults', { drug_id: item.value }, function (res) {
          for (var name in res) {
              if (name === 'dose') {
                  $row.find('[name$="[' + name +']"]').attr('placeholder', res[name]);
                  $row.find('[name$="[units]"]').val(res[name]);
              } else {
                  $row.find('[name$="[' + name +']"]').val(res[name]).change();
              }
          }
      });
  };

  /**
   * From the tags on the given item, retrieve the associated risks and update the core
   * register accordingly.
   *
   * @param item
   */
  HistoryMedicationsController.prototype.processRisks = function(item)
  {
      if (!item.hasOwnProperty('tags')) {
          return;
      }
      var self = this;
      $.getJSON('/OphCiExamination/Risks/forTags', { tag_ids: item.tags.join(",") }, function (res) {
          self.addDrugForRisks(item.name, res);
          self.updateCoreRisks();
      });
  };

  /**
   * Add this drug name to the internal register of risks.
   *
   * @param drugName
   * @param risks
   */
  HistoryMedicationsController.prototype.addDrugForRisks = function(drugName, risks)
  {
      for (var i in risks) {
          var risk = risks[i];
          if (!this.drugsByRisk.hasOwnProperty(risk)) {
              this.drugsByRisk[risk] = [drugName];
          } else {
              if ($.inArray(drugName, this.drugsByRisk[risk]) === -1) {
                  this.drugsByRisk[risk].push(drugName);
              }
          }
      }
  };

  /**
   * Update the core record of risks from history medication with the current stored set
   */
  HistoryMedicationsController.prototype.updateCoreRisks = function()
  {
      var genericStructure = [];
      for (var id in this.drugsByRisk) {
          if (this.drugsByRisk.hasOwnProperty(id)) {
              genericStructure.push([id, this.drugsByRisk[id]]);
          }
      }

      exports.HistoryRisks.setForSource(genericStructure, this.$element);
  };

  HistoryMedicationsController.prototype.resetSearchRow = function($container, showSearch)
  {
      if (showSearch === undefined)
          showSearch = true;

      $container.find(this.options.asTypedFieldSelector).val('');
      $container.find(this.options.drugFieldSelector).val('');
      $container.find(this.options.medicationFieldSelector).val('');
      $container.find(this.options.medicationNameSelector).text('');
      $container.find(this.options.drugSelectSelector).val('');
      if (showSearch) {
          $container.find(this.options.medicationDisplaySelector).hide();
          $container.find(this.options.medicationSearchSelector).show();
          $container.find(this.options.drugSelectSelector).show();
      }
  };

  HistoryMedicationsController.prototype.showDate = function($row, $type)
  {
    var $wrapper = $row.find('.' + $type + '-date-wrapper');
    $wrapper.show();
    var $fuzzyFieldset = $wrapper.parents('fieldset');
    var date = this.dateFromFuzzyFieldSet($fuzzyFieldset);
    $fuzzyFieldset.find('input[type="hidden"]').val(date);
    $fuzzyFieldset.find('.enable').hide();
    $fuzzyFieldset.find('.cancel').show();
  }

  HistoryMedicationsController.prototype.cancelDate = function($row, $type)
  {
    var $wrapper = $row.find('.' + $type + '-date-wrapper');
    $wrapper.hide();
    var $fuzzyFieldset = $wrapper.parents('fieldset');
    $fuzzyFieldset.find('input[type="hidden"]').val('');
    $fuzzyFieldset.find('.enable').show();
    $fuzzyFieldset.find('.cancel').hide();
  };

  HistoryMedicationsController.prototype.updateRowRouteOptions = function($row)
  {
      var $routeOptionWrapper = $row.find(this.options.routeOptionWrapperSelector);
      $routeOptionWrapper.hide();
      $routeOptionWrapper.find('option').each(function() {
          if ($(this).val().length) {
              $(this).remove();
          }
      });
      var value = $row.find(this.options.routeFieldSelector + ' option:selected').val();
      if (value != "") {
          $.getJSON(this.options.routeOptionSource, {route_id: value}, function(data) {
              if (data.length) {
                  var $select = $routeOptionWrapper.find('select');
                  $.each(data, function(i, item) {
                      $select.append('<option value="' + item.id +'">' + item.name + '</option>');
                  });
                  $routeOptionWrapper.show();
              }
          })
      }
  };

  HistoryMedicationsController.prototype.createRow = function(data)
  {
    if (data === undefined)
      data = {};

    data['row_count'] = OpenEyes.Util.getNextDataKey( this.$element.find('table tbody tr'), 'key');
    return Mustache.render(
      this.templateText,
      data
    );
  };

  HistoryMedicationsController.prototype.addEntry = function()
  {
    var row = this.createRow();
    this.$table.find('tbody').append(row);
    this.initialiseRow(this.$table.find('tbody tr:last'));
  };

    /**
     * @TODO: should be common function across history elements
     * @param fieldset
     * @returns {*}
     */
    HistoryMedicationsController.prototype.dateFromFuzzyFieldSet = function(fieldset)
    {
        res = fieldset.find('select.fuzzy_year').val();
        var month = parseInt(fieldset.find('select.fuzzy_month option:selected').val());
        res += '-' + ((month < 10) ? '0' + month.toString() : month.toString());
        var day = parseInt(fieldset.find('select.fuzzy_day option:selected').val());
        res += '-' + ((day < 10) ? '0' + day.toString() : day.toString());

        return res;
    };

    HistoryMedicationsController.prototype.showStopped = function()
    {
        this.$table.find('tr.originally-stopped').show();
        this.$element.find('.show-stopped').hide();
        this.$element.find('.hide-stopped').show();
    };

    HistoryMedicationsController.prototype.hideStopped = function()
    {
        this.$table.find('tr.originally-stopped').hide();
        this.$element.find('.show-stopped').show();
        this.$element.find('.hide-stopped').hide();
    };


  exports.HistoryMedicationsController = HistoryMedicationsController;
})(OpenEyes.OphCiExamination);

(function(exports) {
  function HistoryMedicationsViewController(options) {
    this.options = $.extend(true, {}, HistoryMedicationsViewController._defaultOptions, options);
    this.$element = this.options.element;
    this.initialiseTriggers();
  }

  HistoryMedicationsViewController._defaultOptions = {
    modelName: 'OEModule_OphCiExamination_models_HistoryMedications',
    element: undefined,
    detailToggleSelector: '.detail-toggle',
    kindToggleSelector: '.kind-toggle'
  };

  HistoryMedicationsViewController.prototype.initialiseTriggers = function()
  {
    var controller = this;

    controller.$element.on('click', controller.options.detailToggleSelector, function(e) {
      e.preventDefault();
      var $dataDisplay = controller.$element.find('.' + $(this).data('kind'));
      $dataDisplay.find('.detail').toggle();
      $dataDisplay.find('.simple').toggle();
      $(this).find('.fa').toggleClass('fa-expand fa-compress');
    });

    controller.$element.on('click', controller.options.kindToggleSelector, function(e) {
        e.preventDefault();
        var $kindDisplay = controller.$element.find('.' + $(this).data('kind') + '-kind');
        $kindDisplay.toggle();
        if ($kindDisplay.is(':visible')) {
            // hide the show toggle
            controller.$element.find(controller.options.kindToggleSelector + '.show').hide();
        } else {
            controller.$element.find(controller.options.kindToggleSelector + '.show').show();
        }

    });
  };

  exports.HistoryMedicationsViewController = HistoryMedicationsViewController;
})(OpenEyes.OphCiExamination);