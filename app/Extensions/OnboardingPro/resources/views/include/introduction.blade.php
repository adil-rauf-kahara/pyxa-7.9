@php
    $steps = \App\Models\Extensions\Introduction::getFormattedSteps();
    $styles = App\Extensions\OnboardingPro\System\Models\IntroductionStyle::first();
    $styles_string = '';
    $title_styles = '';
    $description_styles = '';
    $dark_styles_string = '';
    $dark_title_styles = '';
    $dark_description_styles = '';

    if (!empty($styles['background_color'])) {
        $styles_string .= '.shepherd-element, .shepherd-arrow:before { background-color: ' . $styles['background_color'] . '; }';
    }

    if (!empty($styles['title_size'])) {
        $title_styles .= 'font-size: ' . $styles['title_size'] . 'px;';
    }
    if (!empty($styles['title_color'])) {
        $title_styles .= 'color: ' . $styles['title_color'] . ';';
    }
    if (!empty($title_styles)) {
        $styles_string .= '.shepherd-element h3 { ' . $title_styles . ' }';
    }

    if (!empty($styles['description_size'])) {
        $description_styles .= 'font-size: ' . $styles['description_size'] . 'px;';
    }
    if (!empty($styles['description_color'])) {
        $description_styles .= 'color: ' . $styles['description_color'] . ';';
    }
    if (!empty($description_styles)) {
        $styles_string .= '.shepherd-text { ' . $description_styles . ' }';
    }

    if (!empty($styles['dark_background_color'])) {
        $dark_styles_string .= '.theme-dark .shepherd-element, .theme-dark .shepherd-arrow:before { background-color: ' . $styles['dark_background_color'] . '; }';
    }

    if (!empty($styles['dark_title_color'])) {
        $dark_title_styles .= 'color: ' . $styles['dark_title_color'] . ';';
    }
    if (!empty($dark_title_styles)) {
        $dark_styles_string .= '.theme-dark .shepherd-element h3 { ' . $dark_title_styles . ' }';
    }

    if (!empty($styles['dark_description_color'])) {
        $dark_description_styles .= 'color: ' . $styles['dark_description_color'] . ';';
    }
    if (!empty($dark_description_styles)) {
        $dark_styles_string .= '.theme-dark .shepherd-text { ' . $dark_description_styles . ' }';
    }

    $styles_string .= $dark_styles_string;
@endphp

@if (filled($styles_string))
    <style>
        {{ $styles_string }}
    </style>
@endif

<style>
    @keyframes lqdIntroDotFill {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    .lqd-intro-slideshow {
        display: grid;
    }

    .lqd-intro-slideshow-item {
        grid-column: 1 / 1;
        grid-row: 1 / 1;
    }

    .lqd-intro-slideshow.autoplay-on .lqd-intro-slideshow-dot.active:before {
        animation: lqdIntroDotFill 3500ms linear;
    }
</style>

@if (auth()->user()->tour_seen == 0 && \App\Helpers\Classes\Helper::setting('tour_seen') == 1)
    @push('css')
        <link
            rel="stylesheet"
            href="{{ custom_theme_url('/assets/libs/shepherd/shepherd.css') }}"
        />
    @endpush

    <script type="module">
        import Shepherd from 'https://cdn.jsdelivr.net/npm/shepherd.js@13.0.0/dist/esm/shepherd.mjs';
        import {
            flip,
            shift,
            offset
        } from 'https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.12/+esm';

        const navbar = document.querySelector('.lqd-navbar');
        let changedNavbarPosition = false;
        let navbarStyle = null;
        let slideshowTimeout = 3500;
        let steps = [];

        function removeNavbarStyles() {
            if (!navbar) return;

            navbar.style.position = '';
            navbar.style.height = '';
        }

        function getSlideshowButton(index) {
            return `<button class="lqd-intro-slideshow-dot before:[animation-duration:${slideshowTimeout}ms] relative inline-block size-2 rounded-full bg-heading-foreground/10 transition-all before:absolute before:inset-0 before:origin-left before:rounded-full before:bg-heading-foreground/50 before:[transform:scaleX(0)] after:absolute after:-inset-1 [&.active]:w-4 group-[&.autoplay-off]:[&.active]:before:scale-x-100 group-[&.autoplay-off]:[&.active]:before:transition-transform" :class="{ active: activeIndex === ${index} }" type="button" data-index="${index}" @click.prevent="activeIndex = ${index}; autoplay = false;" title="{{ __('Show Slide ${index}') }}"></button>`;
        }

        function getSlideItemTransition() {
            return `x-transition:enter="transition" x-transition:enter-start="opacity-0 -translate-x-2" x-transition:enter-end="opacity-100 translate-x-0" x-transition:leave="transition" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-2"`;
        }

        async function buildSteps(tour) {
            const lqdSteps = @json($steps);

            steps = await Promise.all(lqdSteps.map(async (step, index) => {
                const buttons = [{
                        text: index === 0 ? '{{ __('Skip Tour') }}' : '{{ __('Back') }}',
                        classes: 'shepherd-btn-back',
                        action: index === 0 ? tour.cancel : tour.back
                    },
                    {
                        text: index === 0 ? '{{ __('Take a Quick Tour') }}' : index === lqdSteps.length - 1 ? '{{ __('Go to Dashboard') }}' :
                            '{{ __('Next') }}',
                        classes: 'shepherd-btn-next',
                        action: tour.next
                    }
                ];

                let steps = '';

                step.id = `tour-step-${index}`;
                step.intro = step.intro || '';

                if (step.title) {
                    step.intro = `<h3>${step.title}</h3> <p>${step.intro}</p>`;

                    step.title = null;
                }

                if (step.file_url) {
                    await new Promise(resolve => {
                        const img = new Image();

                        img.onload = function() {
                            step.intro =
                                `<img src="${step.file_url}" width="${img.naturalWidth}" height="${img.naturalHeight}" /> ${step.title ? `${step.intro}` : `<p>${step.intro}</p>`}`;
                            resolve();
                        };

                        img.src = step.file_url;
                    });
                }

                // if it has slideshow
                if (step.steps?.length) {
                    await Promise.all(step.steps.map(async (s, i) => {
                        if (s.file_url) {
                            await new Promise(resolve => {
                                const img = new Image();
                                img.onload = function() {
                                    steps +=
                                        `<div class="lqd-intro-slideshow-item" x-show="activeIndex === ${i + 1}" ${getSlideItemTransition()}><img src="${s.file_url}" width="${img.naturalWidth}" height="${img.naturalHeight}" />${s.title ? `<h3>${s.title}</h3>` : ''}${s.intro ? `<p>${s.intro}</p>` : ''}</div>`;
                                    resolve();
                                };
                                img.src = s.file_url;
                            });
                        } else {
                            steps +=
                                `<div class="lqd-intro-slideshow-item" x-show="activeIndex === ${i + 1}" ${getSlideItemTransition()}>${s.title ? `<h3>${s.title}</h3>` : ''}${s.intro ? `<p>${s.intro}</p>` : ''}</div>`;
                        }
                    }));

                    // wrapper and items markup
                    step.intro =
                        `<div class="lqd-intro-slideshow group autoplay-on" :class="{ 'autoplay-on': autoplay, 'autoplay-off': !autoplay }" x-data="{ itemsLength: ${step.steps.length}, autoplay: true, activeIndex: 0, init() { this.interval = setInterval(() => { if( this.autoplay ) { this.activeIndex = (this.activeIndex + 1) % (this.itemsLength + 1); } else { clearInterval(this.interval) } }, ${slideshowTimeout}) } }">
								<div class="lqd-intro-slideshow-item" x-show="activeIndex === 0" ${getSlideItemTransition()}>${step.intro}</div>
									${steps}`;

                    // pagination dots markup
                    step.intro +=
                        `<div class="lqd-intro-slideshow-dots -order-1 flex items-center gap-1.5 absolute bottom-8 start-5 z-10 whitespace-nowrap">${getSlideshowButton(0)} ${step.steps.map((step, i) => getSlideshowButton(i + 1)).join('')}</div>`;

                    // close wrapper
                    step.intro += '</div>';
                }

                if (step.element) {
                    step.attachTo = {
                        element: step.element,
                        on: step.attachToOn || 'top-start'
                    }
                }

                if (step.intro) {
                    step.text = step.intro;
                }

                step.buttons = buttons;

                return step;
            }));
        }

        async function initTour() {
            const tour = new Shepherd.Tour({
                useModalOverlay: true,
                defaultStepOptions: {
                    classes: 'lqd-tour',
                    scrollTo: {
                        behavior: 'smooth',
                        block: 'center'
                    },
                    cancelIcon: {
                        enabled: true
                    },
                    modalOverlayOpeningPadding: 2,
                    modalOverlayOpeningRadius: 10,
                    floatingUIOptions: {
                        middleware: [
                            flip({
                                fallbackPlacements: ['top', 'right', 'left', 'bottom'],
                                padding: 12
                            }),
                            shift({
                                mainAxis: 12,
                                padding: 16
                            }),
                            offset({
                                mainAxis: 12
                            })
                        ]
                    },
                }
            });

            await buildSteps(tour);

            tour.addSteps(steps);

            tour.on('start', () => {
                navbarStyle = getComputedStyle(navbar);
                if (!changedNavbarPosition && navbarStyle.position === 'sticky') {
                    navbar.style.position = 'relative';
                    navbar.style.height = 'auto';
                    changedNavbarPosition = true;
                }
            });

            tour.on('show', ({
                step
            }) => {
                setTimeout(() => {
                    if (step.target == null && step.el) {
                        step.el.style.transition = 'none';
                    }
                }, 0);
            });

            tour.on('active', () => {
                document.querySelector('.shepherd-modal-overlay-container')?.addEventListener('click', () => {
                    tour.cancel();
                })
            });

            ['complete', 'cancel'].forEach(event => {
                tour.on(event, () => {
                    markTourSeen();
                    removeNavbarStyles();
                });
            });

            tour.start();
        }

        function markTourSeen() {
            fetch('/dashboard/user/mark-tour-seen', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({})
            });
        }

        if (window.innerWidth >= 992) {
            initTour();
        }
    </script>
@endif
