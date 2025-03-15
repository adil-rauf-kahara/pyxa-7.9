import './bootstrap';
import { Alpine, Livewire } from '~vendor/livewire/livewire/dist/livewire.esm';
import ajax from '~nodeModules/@imacrayon/alpine-ajax';
import sort from '~nodeModules/@alpinejs/sort';
import { fetchEventSource } from '@microsoft/fetch-event-source';
import modal from './components/modal';
import clipboard from './components/clipboard';
import assignViewCredits from './components/assignViewCredits';
import openaiRealtime from './components/realtime-frontend/openaiRealtime';
import advancedImageEditor from './components/advancedImageEditor';

window.fetchEventSource = fetchEventSource;
const darkMode = localStorage.getItem('lqdDarkMode');
const docsViewMode = localStorage.getItem('docsViewMode');
const socialMediaPostsViewMode = localStorage.getItem('socialMediaPostsViewMode');
const navbarShrink = localStorage.getItem('lqdNavbarShrinked');
const currentTheme = document.querySelector('body').getAttribute('data-theme');
const lqdFocusModeEnabled = localStorage.getItem(currentTheme +':lqdFocusModeEnabled');


window.collectCreditsToFormData = function (formData) {
	const inputs = document.querySelectorAll('input[name^="entities"]');
	inputs.forEach(input => {
		const name = input.name; // Get the input name
		const value = input.type === 'checkbox' || input.type === 'radio' ? input.checked : input.value; // Get value or checked status
		formData.append(name, value); // Append to the formData object
	});
};

window.Alpine = Alpine;

Alpine.plugin(ajax);
Alpine.plugin(sort);

document.addEventListener('alpine:init', () => {
	const persist = Alpine.$persist;

	Alpine.data('modal', data => modal(data));
	Alpine.data('clipboard', data => clipboard(data));
	Alpine.data('assignViewCredits', data => assignViewCredits(data));

	// Navbar shrink
	Alpine.store('navbarShrink', {
		active: persist(!!navbarShrink).as('lqdNavbarShrinked'),
		toggle(state) {
			this.active = state ? (state === 'shrink' ? true : false) : !this.active;
			document.body.classList.toggle('navbar-shrinked', this.active);
		}
	});

	// Navbar item
	Alpine.data('navbarItem', () => ({
		dropdownOpen: false,
		toggleDropdownOpen(state) {
			this.dropdownOpen = state ? (state === 'collapse' ? true : false) : !this.dropdownOpen;
		},
		item: {
			['x-ref']: 'item',
			['@mouseenter']() {
				if (!Alpine.store('navbarShrink').active) return;
				const rect = this.$el.getBoundingClientRect();
				const dropdown = this.$refs.item.querySelector('.lqd-navbar-dropdown');
				[ 'y', 'height', 'bottom' ].forEach(prop => this.$refs.item.style.setProperty(`--item-${prop}`, `${rect[prop]}px`));

				if (dropdown) {
					const dropdownRect = dropdown.getBoundingClientRect();
					[ 'height' ].forEach(prop => this.$refs.item.style.setProperty(`--dropdown-${prop}`, `${dropdownRect[prop]}px`));
				}
			},
		}
	}));

	// Mobile nav
	Alpine.store('mobileNav', {
		navCollapse: true,
		toggleNav(state) {
			this.navCollapse = state ? (state === 'collapse' ? true : false) : !this.navCollapse;
		},
		templatesCollapse: true,
		toggleTemplates(state) {
			this.templatesCollapse = state ? (state === 'collapse' ? true : false) : !this.templatesCollapse;
		},
		searchCollapse: true,
		toggleSearch(state) {
			this.searchCollapse = state ? (state === 'collapse' ? true : false) : !this.searchCollapse;
		},
	});

	// light/dark mode
	Alpine.store('darkMode', {
		on: persist(!!darkMode).as('lqdDarkMode'),
		toggle() {
			this.on = !this.on;
			document.body.classList.toggle('theme-dark', this.on);
			document.body.classList.toggle('theme-light', !this.on);
		}
	});

	// App loading indicator
	Alpine.store('appLoadingIndicator', {
		showing: false,
		show() {
			this.showing = true;
		},
		hide() {
			this.showing = false;
		},
		toggle() {
			this.showing = !this.showing;
		},
	});

	// Documents view mode
	Alpine.store('docsViewMode', {
		docsViewMode: persist(docsViewMode || 'list').as('docsViewMode'),
		change(mode) {
			this.docsViewMode = mode;
		}
	});

	// Generators filter
	Alpine.store('generatorsFilter', {
		init() {
			const urlParams = new URLSearchParams(window.location.search);
			this.filter = urlParams.get('filter') || 'all';
		},
		filter: 'all',
		changeFilter(filter) {
			if (this.filter === filter) return;
			if (!document.startViewTransition) {
				return this.filter = filter;
			}
			document.startViewTransition(() => this.filter = filter);
		}
	});

	// Documents filter
	Alpine.store('documentsFilter', {
		init() {
			const urlParams = new URLSearchParams(window.location.search);
			this.sort = urlParams.get('sort') || 'created_at';
			this.sortAscDesc = urlParams.get('sortAscDesc') || 'desc';
			this.filter = urlParams.get('filter') || 'all';
			this.page = urlParams.get('page') || '1';
		},
		sort: 'created_at',
		sortAscDesc: 'desc',
		filter: 'all',
		page: '1',
		changeSort(sort) {
			if (sort === this.sort) {
				this.sortAscDesc = this.sortAscDesc === 'desc' ? 'asc' : 'desc';
			} else {
				this.sortAscDesc = 'desc';
			}
			this.sort = sort;
		},
		changeAscDesc(ascDesc) {
			if (this.ascDesc === ascDesc) return;
			this.ascDesc = ascDesc;
		},
		changeFilter(filter) {
			if (this.filter === filter) return;
			this.filter = filter;
		},
		changePage(page) {
			if (page === '>' || page === '<') {
				page = page === '>' ? Number(this.page) + 1 : Number(this.page) - 1;
			}

			if (this.page === page) return;

			this.page = page;
		},
	});

	// Social media posts view mode
	Alpine.store('socialMediaPostsViewMode', {
		socialMediaPostsViewMode: persist(socialMediaPostsViewMode || 'list').as('socialMediaPostsViewMode'),
		change(mode) {
			this.socialMediaPostsViewMode = mode;
		}
	});

	// Social media posts filter
	Alpine.store('socialMediaPostsFilter', {
		init() {
			const urlParams = new URLSearchParams(window.location.search);
			this.sort = urlParams.get('sort') || 'created_at';
			this.sortAscDesc = urlParams.get('sortAscDesc') || 'desc';
			this.filter = urlParams.get('filter') || 'all';
			this.page = urlParams.get('page') || '1';
		},
		sort: 'created_at',
		sortAscDesc: 'desc',
		filter: 'all',
		page: '1',
		changeSort(sort) {
			if (sort === this.sort) {
				this.sortAscDesc = this.sortAscDesc === 'desc' ? 'asc' : 'desc';
			} else {
				this.sortAscDesc = 'desc';
			}
			this.sort = sort;
		},
		changeAscDesc(ascDesc) {
			if (this.ascDesc === ascDesc) return;
			this.ascDesc = ascDesc;
		},
		changeFilter(filter) {
			if (this.filter === filter) return;
			this.filter = filter;
		},
		changePage(page) {
			if (page === '>' || page === '<') {
				page = page === '>' ? Number(this.page) + 1 : Number(this.page) - 1;
			}

			if (this.page === page) return;

			this.page = page;
		},
	});

	// Chats filter
	Alpine.store('chatsFilter', {
		init() {
			const urlParams = new URLSearchParams(window.location.search);
			this.filter = urlParams.get('filter') || 'all';
			this.setSearchStr(urlParams.get('search') || '');
		},
		searchStr: '',
		setSearchStr(str) {
			this.searchStr = str.trim().toLowerCase();
		},
		filter: 'all',
		changeFilter(filter) {
			if (this.filter === filter) return;
			if (!document.startViewTransition) {
				return this.filter = filter;
			}
			document.startViewTransition(() => this.filter = filter);
		}
	});

	// Generator V2
	Alpine.data('generatorV2', () => ({
		itemsSearchStr: '',
		setItemsSearchStr(str) {
			this.itemsSearchStr = str.trim().toLowerCase();
			if (this.itemsSearchStr !== '') {
				this.$el.closest('.lqd-generator-sidebar').classList.add('lqd-showing-search-results');
			} else {
				this.$el.closest('.lqd-generator-sidebar').classList.remove('lqd-showing-search-results');
			}
		},
		sideNavCollapsed: false,
		/**
         *
         * @param {'collapse' | 'expand'} state
         */
		toggleSideNavCollapse(state) {
			this.sideNavCollapsed = state ? (state === 'collapse' ? true : false) : !this.sideNavCollapsed;

			if (this.sideNavCollapsed) {
				tinymce?.activeEditor?.focus();
			}
		},
		generatorStep: 0,
		setGeneratorStep(step) {
			if (step === this.generatorStep) return;
			if (!document.startViewTransition) {
				return this.generatorStep = Number(step);
			}
			document.startViewTransition(() => this.generatorStep = Number(step));
		},
		selectedGenerator: null
	}));

	// Chat
	Alpine.store('mobileChat', {
		sidebarOpen: false,
		toggleSidebar(state) {
			this.sidebarOpen = state ? (state === 'collapse' ? false : false) : !this.sidebarOpen;
		}
	});

	// Dropdown
	Alpine.data('dropdown', ({ triggerType = 'hover' }) => ({
		open: false,
		toggle(state) {
			this.open = state ? (state === 'collapse' ? false : true) : !this.open;
			this.$refs.parent.classList.toggle('lqd-is-active', this.open);
		},
		parent: {
			['@mouseenter']() {
				if (triggerType !== 'hover') return;
				this.toggle('expand');
			},
			['@mouseleave']() {
				if (triggerType !== 'hover') return;
				this.toggle('collapse');
			},
			['@click.outside']() {
				this.toggle('collapse');
			},
		},
		trigger: {
			['@click.prevent']() {
				// we need to be able to toggle dropdown when focus/enter key is pressed
				// if (triggerType !== 'click') return;
				this.toggle();
			},
		},
		dropdown: {}
	}));

	// Notifications
	Alpine.store('notifications', {
		notifications: [],
		loading: false,
		add(notification) {
			this.notifications.unshift(notification);
		},
		remove(index) {
			this.notifications.splice(index, 1);
		},
		markThenHref(notification) {
			const index = this.notifications.indexOf(notification);
			if (index === -1) return;
			var formData = new FormData();
			formData.append('id', notification.id);

			this.loading = true;

			$.ajax({
				url: '/dashboard/notifications/mark-as-read',
				type: 'POST',
				data: formData,
				cache: false,
				contentType: false,
				processData: false,
				success: data => {
				},
				error: error => {
					console.error(error);
				},
				complete: () => {
					this.markAsRead(index);
					window.location = notification.link;
					this.loading = false;
				}
			});
		},
		markAsRead(index) {
			this.notifications = this.notifications.map((notification, i) => {
				if (i === index) {
					notification.unread = false;
				}
				return notification;
			});
		},
		markAllAsRead() {
			this.loading = true;
			$.ajax({
				url: '/dashboard/notifications/mark-as-read',
				type: 'POST',
				success: response => {
					if (response.success) {
						this.notifications.forEach((notification, index) => {
							this.markAsRead(index);
						});
					}
				},
				error: error => {
					console.error(error);
				},
				complete: () => {
					this.loading = false;
				}
			});
		},
		setNotifications(notifications) {
			this.notifications = notifications;
		},
		hasUnread: function () {
			return this.notifications.some(notification => notification.unread);
		}
	});
	Alpine.data('notifications', notifications => ({
		notifications: notifications || [],
	}));

	// Focus Mode
	Alpine.store('focusMode', {
		active: Alpine.$persist(!!lqdFocusModeEnabled).as(currentTheme +':lqdFocusModeEnabled'),
		toggle(state) {

			console.log(currentTheme);
			this.active = state ? (state === 'activate' ? true : false) : !this.active;

			document.body.classList.toggle('focus-mode', this.active);
		},
	});

	// Number Counter Component
	Alpine.data('numberCounter', ({ value = 0, options = {} }) => ({
		value: value,
		options: {
			delay: 0,
			...options
		},
		/**
		 * @type {IntersectionObserver | null}
		 */
		io: null,
		numberWrappers: [],
		numberCols: [],
		numberAnimators: [],
		init() {
			this.$el.innerHTML = '';
			this.buildMarkup();
			this.setupIO();
		},
		updateValue({ value, options = {} }) {
			if ( this.value === value ) return;

			this.value = value;
			this.options = {
				...this.options,
				...options
			};

			this.buildMarkup();
			this.setupIO();
		},
		buildMarkup() {
			const value = this.value.toString().split('');
			const currentNumberWrappers = this.$el.querySelectorAll('.lqd-number-counter-numbers-wrap');

			function buildNumberSpans() {
				return Array.from({ length: 10 }, (_, i) => `<span class="lqd-number-counter-number inline-flex h-full justify-center">${i}</span>`).join('');
			}

			const numberWrappers = value.map((value, index) => {
				const isNumber = !isNaN(value);

				return `<span class="lqd-number-counter-numbers-wrap relative inline-flex h-full w-[1ch]" data-index="${index}" data-value="${value}"><span class="lqd-number-counter-numbers-col absolute start-0 top-[-0.25lh] inline-flex h-[1.5lh] w-full flex-col overflow-hidden py-[0.25lh]"><span class="lqd-number-counter-numbers-animator inline-flex w-full h-full flex-col" data-is-number="${isNumber}" data-value="${value}">${isNumber ? buildNumberSpans() : value }</span></span></span>`;
			});

			numberWrappers.forEach((wrapper, index) => {
				const val = value[index];
				const existingEl = currentNumberWrappers[index];
				const isNumber = !isNaN(val);

				if ( existingEl ) {
					const animatorEl = existingEl.querySelector('.lqd-number-counter-numbers-animator');

					existingEl.setAttribute('data-value', val);

					animatorEl.setAttribute('data-value', val);
					animatorEl.setAttribute('data-is-number', isNumber);
					if (animatorEl.getAttribute('data-is-number') === 'true' && isNumber) {
						if (animatorEl.innerHTML !== buildNumberSpans()) {
							animatorEl.innerHTML = buildNumberSpans();
						}
					} else if (animatorEl.innerHTML !== val) {
						animatorEl.innerHTML = val;
					}

					return;
				}

				this.$el.insertAdjacentHTML('beforeend', wrapper);

				if ( currentNumberWrappers.length ) {
					const currentNumberWrapper = this.$el.querySelector(`.lqd-number-counter-numbers-wrap[data-index="${index}"]`);

					currentNumberWrapper.animate([
						{ translate: '0 0.25lh', opacity: 0 },
						{ translate: '0 0', opacity: 1 },
					], {
						duration: 250,
						easing: 'ease',
						fill: 'both'
					});
				}
			});

			// Remove extra currentNumberWrappers
			if (currentNumberWrappers.length > value.length) {
				for (let i = value.length; i < currentNumberWrappers.length; i++) {
					currentNumberWrappers[i].animate([
						{ translate: '0 -0.25lh', opacity: 0 },
					], {
						duration: 250,
						easing: 'ease',
						fill: 'both'
					}).onfinish = () => {
						currentNumberWrappers[i].remove();
					};
				}
			}

			this.numberWrappers = this.$el.querySelectorAll('.lqd-number-counter-numbers-wrap');
			this.numberCols = this.$el.querySelectorAll('.lqd-number-counter-numbers-col');
			this.numberAnimators = this.$el.querySelectorAll('.lqd-number-counter-numbers-animator');
		},
		setupIO() {
			this.io = new IntersectionObserver(([ entry ], observer) => {
				if ( entry.isIntersecting ) {
					observer.disconnect();
					this.animate();
				}
			});

			this.io.observe(this.$el);
		},
		animate() {
			this.numberAnimators.forEach(el => {
				const isNumber = el.getAttribute('data-is-number') === 'true';

				if ( !isNumber ) return;

				const value = el.getAttribute('data-value');

				el.animate([
					// {
					// 	translate: '0 0',
					// },
					{
						translate: `0 ${value * 100 * -1}%`
					}
				], {
					duration: 800,
					delay: this.options.delay,
					easing: 'cubic-bezier(.47,1.09,.69,1.07)',
					fill: 'both'
				});
			});
		}
	}));

	// OpenAI Realtime
	Alpine.data('openaiRealtime', openaiRealtime);

	// Advanced Image Editor
	Alpine.data('advancedImageEditor', advancedImageEditor);
});

Livewire.start();
