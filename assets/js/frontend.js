(function () {
	'use strict';

	function pad(value) {
		return String(Math.max(0, value)).padStart(2, '0');
	}

	function getExpiration(config) {
		var now = Date.now();

		if (config.mode === 'fixed') {
			return parseInt(config.fixedTimestamp || 0, 10);
		}

		try {
			var stored = window.localStorage.getItem(config.storageKey);
			if (stored) {
				return parseInt(stored, 10);
			}

			var expiration = now + (parseInt(config.duration || 3600, 10) * 1000);
			window.localStorage.setItem(config.storageKey, String(expiration));
			return expiration;
		} catch (error) {
			return now + (parseInt(config.duration || 3600, 10) * 1000);
		}
	}

	function updateVisibility(config, expired) {
		var beforeItems = document.querySelectorAll('.' + config.beforeClass);
		var afterItems = document.querySelectorAll('.' + config.afterClass);

		beforeItems.forEach(function (item) {
			if (expired) {
				item.style.setProperty('display', 'none', 'important');
			} else {
				item.style.removeProperty('display');
			}
		});

		afterItems.forEach(function (item) {
			if (expired) {
				item.style.setProperty('display', 'block', 'important');
			} else {
				item.style.setProperty('display', 'none', 'important');
			}
		});
	}

	function normalizeUrl(url) {
		try {
			var parsed = new URL(url, window.location.href);
			parsed.hash = '';
			return parsed.href.replace(/\/$/, '');
		} catch (error) {
			return '';
		}
	}

	function isCurrentUrl(url) {
		var target = normalizeUrl(url);
		var current = normalizeUrl(window.location.href);
		return target && current && target === current;
	}

	function render(container, totalSeconds) {
		var days = Math.floor(totalSeconds / 86400);
		var hours = Math.floor((totalSeconds % 86400) / 3600);
		var minutes = Math.floor((totalSeconds % 3600) / 60);
		var seconds = totalSeconds % 60;

		var values = {
			days: days,
			hours: hours,
			minutes: minutes,
			seconds: seconds
		};

		container.querySelectorAll('[data-acvs-unit]').forEach(function (unit) {
			var key = unit.getAttribute('data-acvs-unit');
			var number = unit.querySelector('.acvs-countdown__number');
			if (number && Object.prototype.hasOwnProperty.call(values, key)) {
				number.textContent = pad(values[key]);
			}
		});
	}

	function initCountdown(container) {
		var config;
		try {
			config = JSON.parse(container.getAttribute('data-acvs-config'));
		} catch (error) {
			return;
		}

		var expiration = getExpiration(config);

		function tick() {
			var remaining = Math.floor((expiration - Date.now()) / 1000);
			var expired = remaining <= 0;

			if (expired) {
				remaining = 0;
				container.classList.add('acvs-countdown--expired');
				if (config.action === 'visibility') {
					updateVisibility(config, true);
				}
				render(container, 0);

				if (config.expiredDisplay === 'hide') {
					container.classList.add('acvs-countdown--hidden');
				}

				if (config.action === 'redirect' && config.redirectUrl) {
					if (isCurrentUrl(config.redirectUrl)) {
						container.classList.add('acvs-countdown--hidden');
						return false;
					}

					window.location.replace(config.redirectUrl);
					return false;
				}

				return false;
			}

			if (config.action === 'visibility') {
				updateVisibility(config, false);
			}
			render(container, remaining);
			return true;
		}

		if (tick()) {
			var interval = window.setInterval(function () {
				if (!tick()) {
					window.clearInterval(interval);
				}
			}, 1000);
		}
	}

	document.addEventListener('DOMContentLoaded', function () {
		document.querySelectorAll('.acvs-countdown[data-acvs-config]').forEach(initCountdown);
	});
}());
