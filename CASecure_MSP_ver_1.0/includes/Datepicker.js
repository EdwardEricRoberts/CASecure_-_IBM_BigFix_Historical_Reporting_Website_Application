(function () {
			var input = document.getElementById('my-input');

			var datepicker = new TheDatepicker.Datepicker(input);
			datepicker.render();

			var updateSetting = function (settingInput, callback) {
				settingInput.className = '';
				try {
					callback();
					datepicker.render();
				} catch (error) {
					console.error(error);
					settingInput.className = 'invalid';
				}
			};

			var createCallback = function (argumentsString, body) {
				var wrap = function () {
					return '{ return function( ' + argumentsString + ' ){ ' + body + ' } };'
				};
				return (new Function(wrap()))();
			};

			var updateListener = function(eventName, body, argumentsString) {
				eventName = eventName.charAt(0).toUpperCase() + eventName.slice(1);

				datepicker.options['off' + eventName]();

				if (body === '') {
					return;
				}

				datepicker.options['on' + eventName](createCallback(argumentsString, body));
			};

			var initialDateInput = document.getElementById('initialDate');
			initialDateInput.onchange = function () {
				updateSetting(initialDateInput, function () {
					datepicker.options.setInitialDate(initialDateInput.value !== '' ? initialDateInput.value : null);
				});
			};

			var initialMonthInput = document.getElementById('initialMonth');
			initialMonthInput.onchange = function () {
				updateSetting(initialMonthInput, function () {
					datepicker.options.setInitialMonth(initialMonthInput.value !== '' ? initialMonthInput.value : null);
				});
			};

			var openInput = document.getElementById('open');
			openInput.onclick = function () {
				datepicker.open();
			};

			var closeInput = document.getElementById('close');
			closeInput.onclick = function () {
				datepicker.close();
			};

			var destroyInput = document.getElementById('destroy');
			destroyInput.onclick = function () {
				datepicker.destroy();
			};

			var hideOnBlurInput = document.getElementById('hideOnBlur');
			hideOnBlurInput.onchange = function () {
				updateSetting(hideOnBlurInput, function () {
					datepicker.options.setHideOnBlur(hideOnBlurInput.checked);
				});
			};

			var hideOnSelectInput = document.getElementById('hideOnSelect');
			hideOnSelectInput.onchange = function () {
				updateSetting(hideOnSelectInput, function () {
					datepicker.options.setHideOnSelect(hideOnSelectInput.checked);
				});
			};

			var inputFormatInput = document.getElementById('inputFormat');
			inputFormatInput.onchange = function () {
				updateSetting(inputFormatInput, function () {
					datepicker.options.setInputFormat(inputFormatInput.value);
				});
			};

			var firstDayOfWeekInput = document.getElementById('firstDayOfWeek');
			firstDayOfWeekInput.onchange = function () {
				updateSetting(firstDayOfWeekInput, function () {
					datepicker.options.setFirstDayOfWeek(firstDayOfWeekInput.value);
				});
			};

			var minDateInput = document.getElementById('minDate');
			minDateInput.onchange = function () {
				updateSetting(minDateInput, function () {
					datepicker.options.setMinDate(minDateInput.value !== '' ? minDateInput.value : null);
				});
			};

			var maxDateInput = document.getElementById('maxDate');
			maxDateInput.onchange = function () {
				updateSetting(maxDateInput, function () {
					datepicker.options.setMaxDate(maxDateInput.value !== '' ? maxDateInput.value : null);
				});
			};

			var dropdownItemsLimitInput = document.getElementById('dropdownItemsLimit');
			dropdownItemsLimitInput.onchange = function () {
				updateSetting(dropdownItemsLimitInput, function () {
					datepicker.options.setDropdownItemsLimit(dropdownItemsLimitInput.value !== '' ? dropdownItemsLimitInput.value : null);
				});
			};

			var daysOutOfMonthVisibleInput = document.getElementById('daysOutOfMonthVisible');
			daysOutOfMonthVisibleInput.onchange = function () {
				updateSetting(daysOutOfMonthVisibleInput, function () {
					datepicker.options.setDaysOutOfMonthVisible(daysOutOfMonthVisibleInput.checked);
				});
			};

			var fixedRowsCountInput = document.getElementById('fixedRowsCount');
			fixedRowsCountInput.onchange = function () {
				updateSetting(fixedRowsCountInput, function () {
					datepicker.options.setFixedRowsCount(fixedRowsCountInput.checked);
				});
			};

			var toggleSelectionInput = document.getElementById('toggleSelection');
			toggleSelectionInput.onchange = function () {
				updateSetting(toggleSelectionInput, function () {
					datepicker.options.setToggleSelection(toggleSelectionInput.checked);
				});
			};

			var showDeselectButtonInput = document.getElementById('showDeselectButton');
			showDeselectButtonInput.onchange = function () {
				updateSetting(showDeselectButtonInput, function () {
					datepicker.options.setShowDeselectButton(showDeselectButtonInput.checked);
				});
			};

			var allowEmptyInput = document.getElementById('allowEmpty');
			allowEmptyInput.onchange = function () {
				updateSetting(allowEmptyInput, function () {
					datepicker.options.setAllowEmpty(allowEmptyInput.checked);
				});
			};

			var showCloseButtonInput = document.getElementById('showCloseButton');
			showCloseButtonInput.onchange = function () {
				updateSetting(showCloseButtonInput, function () {
					datepicker.options.setShowCloseButton(showCloseButtonInput.checked);
				});
			};

			var titleInput = document.getElementById('title');
			titleInput.onchange = function () {
				updateSetting(titleInput, function () {
					datepicker.options.setTitle(titleInput.value);
				});
			};

			var showResetButtonInput = document.getElementById('showResetButton');
			showResetButtonInput.onchange = function () {
				updateSetting(showResetButtonInput, function () {
					datepicker.options.setShowResetButton(showResetButtonInput.checked);
				});
			};

			var monthAsDropdownInput = document.getElementById('monthAsDropdown');
			monthAsDropdownInput.onchange = function () {
				updateSetting(monthAsDropdownInput, function () {
					datepicker.options.setMonthAsDropdown(monthAsDropdownInput.checked);
				});
			};

			var yearAsDropdownInput = document.getElementById('yearAsDropdown');
			yearAsDropdownInput.onchange = function () {
				updateSetting(yearAsDropdownInput, function () {
					datepicker.options.setYearAsDropdown(yearAsDropdownInput.checked);
				});
			};

			var monthAndYearSeparatedInput = document.getElementById('monthAndYearSeparated');
			monthAndYearSeparatedInput.onchange = function () {
				updateSetting(monthAndYearSeparatedInput, function () {
					datepicker.options.setMonthAndYearSeparated(monthAndYearSeparatedInput.checked);
				});
			};

			var positionFixingInput = document.getElementById('positionFixing');
			positionFixingInput.onchange = function () {
				updateSetting(positionFixingInput, function () {
					datepicker.options.setPositionFixing(positionFixingInput.checked);
				});
			};

			var dateAvailabilityResolverInput = document.getElementById('dateAvailabilityResolver');
			dateAvailabilityResolverInput.onchange = function () {
				if (dateAvailabilityResolverInput.value === '') {
					datepicker.options.setDateAvailabilityResolver(null);
				} else {
					datepicker.options.setDateAvailabilityResolver(createCallback('date', dateAvailabilityResolverInput.value));
				}
				datepicker.render();
			};

			var cellContentResolverInput = document.getElementById('cellContentResolver');
			cellContentResolverInput.onchange = function () {
				if (cellContentResolverInput.value === '') {
					datepicker.options.setCellContentResolver(null);
				} else {
					datepicker.options.setCellContentResolver(createCallback('day', cellContentResolverInput.value));
				}
				datepicker.render();
			};

			var cellClassesResolverInput = document.getElementById('cellClassesResolver');
			cellClassesResolverInput.onchange = function () {
				if (cellClassesResolverInput.value === '') {
					datepicker.options.setCellClassesResolver(null);
				} else {
					datepicker.options.setCellClassesResolver(createCallback('day', cellClassesResolverInput.value));
				}
				datepicker.render();
			};

			var onBeforeSelectInput = document.getElementById('onBeforeSelect');
			onBeforeSelectInput.onchange = function () {
				updateListener('beforeSelect', onBeforeSelectInput.value, 'event, day, previousDay');
			};

			var onSelectInput = document.getElementById('onSelect');
			onSelectInput.onchange = function () {
				updateListener('select', onSelectInput.value, 'event, day, previousDay');
			};

			var onBeforeOpenAndCloseInput = document.getElementById('onBeforeOpenAndClose');
			onBeforeOpenAndCloseInput.onchange = function () {
				updateListener('beforeOpenAndClose', onBeforeOpenAndCloseInput.value, 'event, isOpening');
			};

			var onOpenAndCloseInput = document.getElementById('onOpenAndClose');
			onOpenAndCloseInput.onchange = function () {
				updateListener('openAndClose', onOpenAndCloseInput.value, 'event, isOpening');
			};

			var onBeforeMonthChangeInput = document.getElementById('onBeforeMonthChange');
			onBeforeMonthChangeInput.onchange = function () {
				updateListener('beforeMonthChange', onBeforeMonthChangeInput.value, 'event, month, previousMonth');
			};

			var onMonthChangeInput = document.getElementById('onMonthChange');
			onMonthChangeInput.onchange = function () {
				updateListener('monthChange', onMonthChangeInput.value, 'event, month, previousMonth');
			};

			var classesPrefixInput = document.getElementById('classesPrefix');
			classesPrefixInput.onchange = function () {
				updateSetting(classesPrefixInput, function () {
					datepicker.options.setClassesPrefix(classesPrefixInput.value);
				});
			};

			var todayInput = document.getElementById('today');
			todayInput.onchange = function () {
				updateSetting(todayInput, function () {
					datepicker.options.setToday(todayInput.value);
				});
			};

			var goBackHtmlInput = document.getElementById('goBackHtml');
			goBackHtmlInput.onchange = function () {
				updateSetting(goBackHtmlInput, function () {
					datepicker.options.setGoBackHtml(goBackHtmlInput.value);
				});
			};

			var goForwardHtmlInput = document.getElementById('goForwardHtml');
			goForwardHtmlInput.onchange = function () {
				updateSetting(goForwardHtmlInput, function () {
					datepicker.options.setGoForwardHtml(goForwardHtmlInput.value);
				});
			};

			var closeHtmlInput = document.getElementById('closeHtml');
			closeHtmlInput.onchange = function () {
				updateSetting(closeHtmlInput, function () {
					datepicker.options.setCloseHtml(closeHtmlInput.value);
				});
			};

			var resetHtmlInput = document.getElementById('resetHtml');
			resetHtmlInput.onchange = function () {
				updateSetting(resetHtmlInput, function () {
					datepicker.options.setResetHtml(resetHtmlInput.value);
				});
			};

			var deselectHtmlInput = document.getElementById('deselectHtml');
			deselectHtmlInput.onchange = function () {
				updateSetting(deselectHtmlInput, function () {
					datepicker.options.setReselectHtml(deselectHtmlInput.value);
				});
			};

			for (var i = 0; i < 7; i++) {
				(function () {
					var dayOfWeek = i;
					var dayOfWeekTranslationInput = document.getElementById('dayOfWeekTranslation' + dayOfWeek);
					dayOfWeekTranslationInput.onchange = function () {
						updateSetting(dayOfWeekTranslationInput, function () {
							datepicker.options.translator.setDayOfWeekTranslation(dayOfWeek, dayOfWeekTranslationInput.value);
						});
					};
				})();
			}

			for (var j = 0; j < 12; j++) {
				(function () {
					var month = j;
					var monthTranslationInput = document.getElementById('monthTranslation' + month);
					monthTranslationInput.onchange = function () {
						updateSetting(monthTranslationInput, function () {
							datepicker.options.translator.setMonthTranslation(month, monthTranslationInput.value);
						});
					};
				})();
			}
		})();