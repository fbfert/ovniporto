import { a as ease, c as motion, d as Seal, i as duration, l as react_exports, n as useToast, t as ToastProvider, u as Section } from "./Toast-DbyDChDd.js";
import { c as SaucerShape, f as t, i as Starfield, p as Button, r as Eyebrow } from "./Typography-BT6DGpEB.js";
import { Head, Link, router, usePage } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import { useEffect, useRef, useState } from "react";
//#region resources/js/Components/Layout/SeoHead.tsx
var DEFAULT_DESCRIPTION = "Astroturismo na Serra Catarinense: comunidade, Livro de avistamentos e lembranças do OVNIPORTO, a futura pista de pouso de Lages, SC.";
/** Title, description, canonical, Open Graph and Twitter Card. Rendered on the server. */
function SeoHead({ title, description = DEFAULT_DESCRIPTION, image = "/og/default.jpg" }) {
	const { appUrl, currentUrl } = usePage().props;
	const fullTitle = title ? `${title} · OVNIPORTO Lages` : "OVNIPORTO Lages · A pista de pouso do planalto";
	const imageUrl = image.startsWith("http") ? image : `${appUrl}${image}`;
	return /* @__PURE__ */ jsxs(Head, {
		title,
		children: [
			/* @__PURE__ */ jsx("meta", {
				"head-key": "description",
				name: "description",
				content: description
			}),
			/* @__PURE__ */ jsx("link", {
				"head-key": "canonical",
				rel: "canonical",
				href: currentUrl
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:type",
				property: "og:type",
				content: "website"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:site_name",
				property: "og:site_name",
				content: "OVNIPORTO Lages"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:locale",
				property: "og:locale",
				content: "pt_BR"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:title",
				property: "og:title",
				content: fullTitle
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:description",
				property: "og:description",
				content: description
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:url",
				property: "og:url",
				content: currentUrl
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:image",
				property: "og:image",
				content: imageUrl
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:image:width",
				property: "og:image:width",
				content: "1200"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "og:image:height",
				property: "og:image:height",
				content: "630"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "twitter:card",
				name: "twitter:card",
				content: "summary_large_image"
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "twitter:title",
				name: "twitter:title",
				content: fullTitle
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "twitter:description",
				name: "twitter:description",
				content: description
			}),
			/* @__PURE__ */ jsx("meta", {
				"head-key": "twitter:image",
				name: "twitter:image",
				content: imageUrl
			})
		]
	});
}
//#endregion
//#region resources/js/Components/Layout/nav.ts
var primaryLinks = [
	{
		href: "/regiao",
		label: t.nav.region
	},
	{
		href: "/o-lugar",
		label: t.nav.place
	},
	{
		href: "/mapa",
		label: t.nav.logbook
	},
	{
		href: "/loja",
		label: t.nav.store
	}
];
var allLinks = [
	...primaryLinks,
	{
		href: "/relatar",
		label: "Relatar avistamento"
	},
	{
		href: "/lenda",
		label: "A lenda"
	},
	{
		href: "/apoie",
		label: "Apoie a pista"
	},
	{
		href: "/obra",
		label: "Diário da obra"
	},
	{
		href: "/comunidade",
		label: "Comunidade"
	},
	{
		href: "/faq",
		label: "Perguntas frequentes"
	}
];
var JOIN_HREF = "/comunidade";
//#endregion
//#region resources/js/Components/Layout/Footer.tsx
var MAP_URL = "https://www.openstreetmap.org/?mlat=-27.85495&mlon=-50.21841#map=14/-27.85495/-50.21841";
function Footer() {
	const content = usePage().props.content;
	const whatsapp = content?.link_whatsapp;
	const instagram = content?.link_instagram;
	const email = content?.contact_email || "contato@ovniporto.tars.art.br";
	return /* @__PURE__ */ jsx("footer", {
		className: "relative overflow-hidden",
		children: /* @__PURE__ */ jsxs(Section, {
			tone: "dark",
			pattern: "stars",
			wave: true,
			innerClassName: "pb-10!",
			children: [
				/* @__PURE__ */ jsx("div", {
					"aria-hidden": true,
					className: "pointer-events-none absolute top-16 left-0 w-full motion-reduce:hidden",
					children: /* @__PURE__ */ jsx("svg", {
						viewBox: "-120 -60 240 90",
						className: "w-20 animate-flyby opacity-0",
						children: /* @__PURE__ */ jsx(SaucerShape, { lightsClassName: "animate-blink" })
					})
				}),
				/* @__PURE__ */ jsx(Eyebrow, {
					tone: "dark",
					className: "text-center text-[clamp(2.2rem,1.4rem+3.6vw,4.4rem)]! rotate-[-3deg]!",
					children: t.brand.signoff
				}),
				/* @__PURE__ */ jsxs("div", {
					className: "mt-16 grid gap-12 border-t border-moonlight/12 pt-12 md:grid-cols-[1.4fr_1fr_1fr]",
					children: [
						/* @__PURE__ */ jsxs("div", {
							className: "flex items-start gap-4",
							children: [/* @__PURE__ */ jsx(Seal, {
								size: "md",
								className: "size-20! shrink-0"
							}), /* @__PURE__ */ jsxs("div", { children: [
								/* @__PURE__ */ jsx("p", {
									className: "font-display text-xl font-extrabold tracking-[0.04em] uppercase",
									children: t.brand.name
								}),
								/* @__PURE__ */ jsx("p", {
									className: "mt-1 text-moonlight/75",
									children: t.brand.tagline
								}),
								/* @__PURE__ */ jsx("p", {
									className: "mt-1 font-script text-xl text-beam-glow",
									children: t.brand.city
								})
							] })]
						}),
						/* @__PURE__ */ jsxs("nav", {
							"aria-label": t.footer.explore,
							children: [/* @__PURE__ */ jsx("h2", {
								className: "text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/60 uppercase",
								children: t.footer.explore
							}), /* @__PURE__ */ jsx("ul", {
								className: "mt-4 grid grid-cols-2 gap-x-4 gap-y-1 md:grid-cols-1",
								children: allLinks.map((link) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx(Link, {
									href: link.href,
									className: "inline-flex min-h-9 items-center text-moonlight/85 hover:text-beam-glow",
									children: link.label
								}) }, link.href))
							})]
						}),
						/* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsx("h2", {
							className: "text-[0.7rem] font-semibold tracking-[0.12em] text-moonlight/60 uppercase",
							children: t.footer.community
						}), /* @__PURE__ */ jsxs("ul", {
							className: "mt-4 space-y-1",
							children: [
								/* @__PURE__ */ jsx("li", { children: whatsapp ? /* @__PURE__ */ jsx("a", {
									href: whatsapp,
									className: "inline-flex min-h-9 items-center hover:text-beam-glow",
									children: "WhatsApp"
								}) : /* @__PURE__ */ jsxs("span", {
									className: "inline-flex min-h-9 items-center text-moonlight/55",
									children: ["WhatsApp · ", t.footer.soon]
								}) }),
								/* @__PURE__ */ jsx("li", { children: instagram ? /* @__PURE__ */ jsx("a", {
									href: instagram,
									className: "inline-flex min-h-9 items-center hover:text-beam-glow",
									children: "Instagram"
								}) : /* @__PURE__ */ jsxs("span", {
									className: "inline-flex min-h-9 items-center text-moonlight/55",
									children: ["Instagram · ", t.footer.soon]
								}) }),
								/* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsx("a", {
									href: `mailto:${email}`,
									className: "inline-flex min-h-9 items-center break-all hover:text-beam-glow",
									children: email
								}) })
							]
						})] })
					]
				}),
				/* @__PURE__ */ jsxs("div", {
					className: "mt-14 flex flex-col gap-4 border-t border-moonlight/12 pt-6 text-sm text-moonlight/70 md:flex-row md:items-center md:justify-between",
					children: [/* @__PURE__ */ jsxs("p", { children: [
						t.brand.location,
						" ·",
						" ",
						/* @__PURE__ */ jsx("a", {
							href: MAP_URL,
							target: "_blank",
							rel: "noopener noreferrer",
							className: "underline decoration-moonlight/40 underline-offset-4 hover:text-beam-glow",
							children: t.footer.map
						})
					] }), /* @__PURE__ */ jsxs("p", {
						className: "flex flex-wrap gap-x-5 gap-y-2",
						children: [
							/* @__PURE__ */ jsx(Link, {
								href: "/privacidade",
								className: "hover:text-beam-glow",
								children: t.footer.privacy
							}),
							/* @__PURE__ */ jsx(Link, {
								href: "/termos",
								className: "hover:text-beam-glow",
								children: t.footer.terms
							}),
							/* @__PURE__ */ jsxs("span", { children: [
								t.footer.madeBy,
								" ",
								/* @__PURE__ */ jsx("a", {
									href: "https://xiax.com.br",
									target: "_blank",
									rel: "noopener noreferrer",
									className: "font-semibold text-moonlight hover:text-beam-glow",
									children: "Xiax"
								})
							] })
						]
					})]
				})
			]
		})
	});
}
//#endregion
//#region resources/js/Components/Layout/MobileMenu.tsx
/** Origin of the circular reveal: the menu button (top-right of the pill). */
var ORIGIN = "calc(100% - 2.6rem) 2.6rem";
/**
* Full-screen night panel that opens as a circle growing out of the menu button.
* Focus is trapped inside; Esc and the close button return focus to the trigger.
*/
function MobileMenu({ open, onClose }) {
	const panelRef = useRef(null);
	useEffect(() => {
		if (!open) return;
		const panel = panelRef.current;
		const previous = document.activeElement;
		document.documentElement.style.overflow = "hidden";
		const focusables = () => Array.from(panel?.querySelectorAll("a[href], button:not([disabled])") ?? []);
		requestAnimationFrame(() => focusables()[0]?.focus());
		const onKey = (event) => {
			if (event.key === "Escape") {
				onClose();
				return;
			}
			if (event.key !== "Tab") return;
			const items = focusables();
			const first = items[0];
			const last = items[items.length - 1];
			if (event.shiftKey && document.activeElement === first) {
				event.preventDefault();
				last?.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first?.focus();
			}
		};
		document.addEventListener("keydown", onKey);
		return () => {
			document.removeEventListener("keydown", onKey);
			document.documentElement.style.overflow = "";
			(previous ?? document.getElementById("menu-trigger"))?.focus();
		};
	}, [open, onClose]);
	return /* @__PURE__ */ jsx(react_exports.AnimatePresence, { children: open && /* @__PURE__ */ jsxs(motion.div, {
		id: "mobile-menu",
		ref: panelRef,
		role: "dialog",
		"aria-modal": "true",
		"aria-label": t.nav.primary,
		"data-tone": "dark",
		initial: { clipPath: `circle(0% at ${ORIGIN})` },
		animate: { clipPath: `circle(150% at ${ORIGIN})` },
		exit: { clipPath: `circle(0% at ${ORIGIN})` },
		transition: {
			duration: .5,
			ease: ease.drawer
		},
		className: "fixed inset-0 z-[60] flex flex-col overflow-y-auto bg-night text-moonlight lg:hidden",
		children: [
			/* @__PURE__ */ jsx(Starfield, {
				className: "absolute inset-0",
				density: "low"
			}),
			/* @__PURE__ */ jsxs("div", {
				className: "relative flex items-center justify-between px-6 pt-6",
				children: [/* @__PURE__ */ jsx("span", {
					className: "font-script text-2xl text-beam-glow",
					children: t.brand.tagline
				}), /* @__PURE__ */ jsx("button", {
					type: "button",
					onClick: onClose,
					"aria-label": t.nav.closeMenu,
					className: "press inline-flex size-11 items-center justify-center rounded-full border border-moonlight/30",
					children: /* @__PURE__ */ jsx("svg", {
						viewBox: "0 0 24 24",
						className: "size-5",
						fill: "none",
						"aria-hidden": true,
						children: /* @__PURE__ */ jsx("path", {
							d: "M6 6l12 12M18 6L6 18",
							stroke: "currentColor",
							strokeWidth: "2",
							strokeLinecap: "round"
						})
					})
				})]
			}),
			/* @__PURE__ */ jsx("ul", {
				className: "relative mt-8 flex flex-col gap-1 px-6",
				children: allLinks.map((link, i) => /* @__PURE__ */ jsx(motion.li, {
					initial: {
						opacity: 0,
						transform: "translateY(14px)"
					},
					animate: {
						opacity: 1,
						transform: "translateY(0px)"
					},
					transition: {
						delay: .12 + i * .04,
						duration: .4,
						ease: ease.snap
					},
					children: /* @__PURE__ */ jsx(Link, {
						href: link.href,
						onClick: onClose,
						className: "block py-2 font-display text-[clamp(1.35rem,6vw,2rem)] leading-tight font-bold tracking-[0.04em] uppercase active:text-beam",
						children: link.label
					})
				}, link.href))
			}),
			/* @__PURE__ */ jsxs("div", {
				className: "relative mt-auto flex flex-col gap-3 px-6 pt-10 pb-[max(2rem,env(safe-area-inset-bottom))]",
				children: [/* @__PURE__ */ jsx(Button, {
					href: "/comunidade",
					size: "lg",
					onClick: onClose,
					children: t.nav.join
				}), /* @__PURE__ */ jsx("p", {
					className: "text-center font-script text-xl text-beam-glow",
					children: t.brand.signoff
				})]
			})
		]
	}) });
}
//#endregion
//#region resources/js/Components/Layout/Header.tsx
/**
* Floating pill. Inverts to night over dark bands (each band carries
* data-tone="dark"), hides while reading downward and returns on the way up.
*/
function Header() {
	const [onDark, setOnDark] = useState(true);
	const [hidden, setHidden] = useState(false);
	const [menuOpen, setMenuOpen] = useState(false);
	const { scrollY } = (0, react_exports.useScroll)();
	const lastY = useRef(0);
	const { url } = usePage();
	(0, react_exports.useMotionValueEvent)(scrollY, "change", (y) => {
		const delta = y - lastY.current;
		if (Math.abs(delta) > 6) {
			setHidden(delta > 0 && y > 120);
			lastY.current = y;
		}
	});
	useEffect(() => {
		const dark = /* @__PURE__ */ new Set();
		const observer = new IntersectionObserver((entries) => {
			for (const entry of entries) if (entry.isIntersecting) dark.add(entry.target);
			else dark.delete(entry.target);
			setOnDark(dark.size > 0);
		}, { rootMargin: "-32px 0px -94% 0px" });
		document.querySelectorAll("[data-tone=\"dark\"]").forEach((el) => observer.observe(el));
		return () => observer.disconnect();
	}, [url]);
	const toneClasses = onDark ? "bg-night/85 text-moonlight border-moonlight/12" : "bg-moonlight/85 text-night border-night/10";
	return /* @__PURE__ */ jsxs(Fragment, { children: [/* @__PURE__ */ jsx(motion.header, {
		initial: false,
		animate: { transform: hidden && !menuOpen ? "translateY(-140%)" : "translateY(0%)" },
		transition: {
			duration: duration.panel,
			ease: ease.snap
		},
		className: "fixed inset-x-0 top-0 z-50 px-3 pt-3 sm:px-4 sm:pt-4",
		children: /* @__PURE__ */ jsxs("nav", {
			"aria-label": t.nav.primary,
			className: `mx-auto grid h-16 max-w-6xl grid-cols-[1fr_auto_1fr] items-center rounded-full border px-2 backdrop-blur-[12px] transition-colors duration-300 ease-snap lg:px-3 ${toneClasses}`,
			children: [
				/* @__PURE__ */ jsx("ul", {
					className: "hidden items-center gap-1 lg:flex",
					children: primaryLinks.map((link) => /* @__PURE__ */ jsx("li", { children: /* @__PURE__ */ jsxs(Link, {
						href: link.href,
						prefetch: true,
						className: "group relative inline-flex h-11 items-center rounded-full px-3 text-[0.92rem] font-medium",
						children: [link.label, /* @__PURE__ */ jsx("span", {
							"aria-hidden": true,
							className: "absolute inset-x-3 bottom-2.5 h-px origin-left scale-x-0 bg-current transition-transform duration-300 ease-snap group-hover:scale-x-100"
						})]
					}) }, link.href))
				}),
				/* @__PURE__ */ jsx("span", { className: "lg:hidden" }),
				/* @__PURE__ */ jsx(Link, {
					href: "/",
					"aria-label": t.nav.home,
					className: "press rounded-full transition-transform duration-500 ease-snap [@media(hover:hover)]:hover:rotate-[-12deg]",
					children: /* @__PURE__ */ jsx(Seal, { size: "sm" })
				}),
				/* @__PURE__ */ jsxs("div", {
					className: "flex items-center justify-end gap-2",
					children: [/* @__PURE__ */ jsx("span", {
						className: "hidden lg:block",
						children: /* @__PURE__ */ jsx(Button, {
							href: JOIN_HREF,
							size: "sm",
							children: t.nav.join
						})
					}), /* @__PURE__ */ jsx("button", {
						type: "button",
						"aria-expanded": menuOpen,
						"aria-controls": "mobile-menu",
						"aria-label": menuOpen ? t.nav.closeMenu : t.nav.openMenu,
						onClick: () => setMenuOpen(true),
						className: "press inline-flex size-11 items-center justify-center rounded-full lg:hidden",
						id: "menu-trigger",
						children: /* @__PURE__ */ jsx("svg", {
							viewBox: "0 0 24 24",
							className: "size-6",
							fill: "none",
							"aria-hidden": true,
							children: /* @__PURE__ */ jsx("path", {
								d: "M4 8h16M4 16h11",
								stroke: "currentColor",
								strokeWidth: "2",
								strokeLinecap: "round"
							})
						})
					})]
				})
			]
		})
	}), /* @__PURE__ */ jsx(MobileMenu, {
		open: menuOpen,
		onClose: () => setMenuOpen(false)
	})] });
}
//#endregion
//#region node_modules/lenis/dist/lenis.mjs
var version = "1.3.26";
/**
* Clamp a value between a minimum and maximum value
*
* @param min Minimum value
* @param input Value to clamp
* @param max Maximum value
* @returns Clamped value
*/
function clamp(min, input, max) {
	return Math.max(min, Math.min(input, max));
}
/**
*  Linearly interpolate between two values using an amount (0 <= t <= 1)
*
* @param x First value
* @param y Second value
* @param t Amount to interpolate (0 <= t <= 1)
* @returns Interpolated value
*/
function lerp(x, y, t) {
	return (1 - t) * x + t * y;
}
/**
* Damp a value over time using a damping factor
* {@link http://www.rorydriscoll.com/2016/03/07/frame-rate-independent-damping-using-lerp/}
*
* @param x Initial value
* @param y Target value
* @param lambda Damping factor
* @param dt Time elapsed since the last update
* @returns Damped value
*/
function damp(x, y, lambda, deltaTime) {
	return lerp(x, y, 1 - Math.exp(-lambda * deltaTime));
}
/**
* Calculate the modulo of the dividend and divisor while keeping the result within the same sign as the divisor
* {@link https://anguscroll.com/just/just-modulo}
*
* @param n Dividend
* @param d Divisor
* @returns Modulo
*/
function modulo(n, d) {
	return (n % d + d) % d;
}
/**
* Animate class to handle value animations with lerping or easing
*
* @example
* const animate = new Animate()
* animate.fromTo(0, 100, { duration: 1, easing: (t) => t })
* animate.advance(0.5) // 50
*/
var Animate = class {
	isRunning = false;
	value = 0;
	from = 0;
	to = 0;
	currentTime = 0;
	lerp;
	duration;
	easing;
	onUpdate;
	/**
	* Advance the animation by the given delta time
	*
	* @param deltaTime - The time in seconds to advance the animation
	*/
	advance(deltaTime) {
		if (!this.isRunning) return;
		let completed = false;
		if (this.duration && this.easing) {
			this.currentTime += deltaTime;
			const linearProgress = clamp(0, this.currentTime / this.duration, 1);
			completed = linearProgress >= 1;
			const easedProgress = completed ? 1 : this.easing(linearProgress);
			this.value = this.from + (this.to - this.from) * easedProgress;
		} else if (this.lerp) {
			this.value = damp(this.value, this.to, this.lerp * 60, deltaTime);
			if (Math.round(this.value) === Math.round(this.to)) {
				this.value = this.to;
				completed = true;
			}
		} else {
			this.value = this.to;
			completed = true;
		}
		if (completed) this.stop();
		this.onUpdate?.(this.value, completed);
	}
	/** Stop the animation */
	stop() {
		this.isRunning = false;
	}
	/**
	* Set up the animation from a starting value to an ending value
	* with optional parameters for lerping, duration, easing, and onUpdate callback
	*
	* @param from - The starting value
	* @param to - The ending value
	* @param options - Options for the animation
	*/
	fromTo(from, to, { lerp, duration, easing, onStart, onUpdate }) {
		this.from = this.value = from;
		this.to = to;
		this.lerp = lerp;
		this.duration = duration;
		this.easing = easing;
		this.currentTime = 0;
		this.isRunning = true;
		onStart?.();
		this.onUpdate = onUpdate;
	}
};
function debounce(callback, delay) {
	let timer;
	return function(...args) {
		clearTimeout(timer);
		timer = setTimeout(() => {
			timer = void 0;
			callback.apply(this, args);
		}, delay);
	};
}
/**
* Dimensions class to handle the size of the content and wrapper
*
* @example
* const dimensions = new Dimensions(wrapper, content)
* dimensions.on('resize', (e) => {
*   console.log(e.width, e.height)
* })
*/
var Dimensions = class {
	width = 0;
	height = 0;
	scrollHeight = 0;
	scrollWidth = 0;
	debouncedResize;
	wrapperResizeObserver;
	contentResizeObserver;
	constructor(wrapper, content, { autoResize = true, debounce: debounceValue = 250 } = {}) {
		this.wrapper = wrapper;
		this.content = content;
		if (autoResize) {
			this.debouncedResize = debounce(this.resize, debounceValue);
			if (this.wrapper instanceof Window) window.addEventListener("resize", this.debouncedResize);
			else {
				this.wrapperResizeObserver = new ResizeObserver(this.debouncedResize);
				this.wrapperResizeObserver.observe(this.wrapper);
			}
			this.contentResizeObserver = new ResizeObserver(this.debouncedResize);
			this.contentResizeObserver.observe(this.content);
		}
		this.resize();
	}
	destroy() {
		this.wrapperResizeObserver?.disconnect();
		this.contentResizeObserver?.disconnect();
		if (this.wrapper === window && this.debouncedResize) window.removeEventListener("resize", this.debouncedResize);
	}
	resize = () => {
		this.onWrapperResize();
		this.onContentResize();
	};
	onWrapperResize = () => {
		if (this.wrapper instanceof Window) {
			this.width = window.innerWidth;
			this.height = window.innerHeight;
		} else {
			this.width = this.wrapper.clientWidth;
			this.height = this.wrapper.clientHeight;
		}
	};
	onContentResize = () => {
		if (this.wrapper instanceof Window) {
			this.scrollHeight = this.content.scrollHeight;
			this.scrollWidth = this.content.scrollWidth;
		} else {
			this.scrollHeight = this.wrapper.scrollHeight;
			this.scrollWidth = this.wrapper.scrollWidth;
		}
	};
	get limit() {
		return {
			x: this.scrollWidth - this.width,
			y: this.scrollHeight - this.height
		};
	}
};
/**
* Emitter class to handle events
* @example
* const emitter = new Emitter()
* emitter.on('event', (data) => {
*   console.log(data)
* })
* emitter.emit('event', 'data')
*/
var Emitter = class {
	events = {};
	/**
	* Emit an event with the given data
	* @param event Event name
	* @param args Data to pass to the event handlers
	*/
	emit(event, ...args) {
		const callbacks = this.events[event] || [];
		for (let i = 0, length = callbacks.length; i < length; i++) callbacks[i]?.(...args);
	}
	/**
	* Add a callback to the event
	* @param event Event name
	* @param cb Callback function
	* @returns Unsubscribe function
	*/
	on(event, cb) {
		if (this.events[event]) this.events[event].push(cb);
		else this.events[event] = [cb];
		return () => {
			this.events[event] = this.events[event]?.filter((i) => cb !== i);
		};
	}
	/**
	* Remove a callback from the event
	* @param event Event name
	* @param callback Callback function
	*/
	off(event, callback) {
		this.events[event] = this.events[event]?.filter((i) => callback !== i);
	}
	/**
	* Remove all event listeners and clean up
	*/
	destroy() {
		this.events = {};
	}
};
var LINE_HEIGHT = 100 / 6;
var listenerOptions = { passive: false };
function getDeltaMultiplier(deltaMode, size) {
	if (deltaMode === 1) return LINE_HEIGHT;
	if (deltaMode === 2) return size;
	return 1;
}
var VirtualScroll = class {
	touchStart = {
		x: 0,
		y: 0
	};
	lastDelta = {
		x: 0,
		y: 0
	};
	window = {
		width: 0,
		height: 0
	};
	emitter = new Emitter();
	constructor(element, options = {
		wheelMultiplier: 1,
		touchMultiplier: 1
	}) {
		this.element = element;
		this.options = options;
		window.addEventListener("resize", this.onWindowResize);
		this.onWindowResize();
		this.element.addEventListener("wheel", this.onWheel, listenerOptions);
		this.element.addEventListener("touchstart", this.onTouchStart, listenerOptions);
		this.element.addEventListener("touchmove", this.onTouchMove, listenerOptions);
		this.element.addEventListener("touchend", this.onTouchEnd, listenerOptions);
	}
	/**
	* Add an event listener for the given event and callback
	*
	* @param event Event name
	* @param callback Callback function
	*/
	on(event, callback) {
		return this.emitter.on(event, callback);
	}
	/** Remove all event listeners and clean up */
	destroy() {
		this.emitter.destroy();
		window.removeEventListener("resize", this.onWindowResize);
		this.element.removeEventListener("wheel", this.onWheel, listenerOptions);
		this.element.removeEventListener("touchstart", this.onTouchStart, listenerOptions);
		this.element.removeEventListener("touchmove", this.onTouchMove, listenerOptions);
		this.element.removeEventListener("touchend", this.onTouchEnd, listenerOptions);
	}
	/**
	* Event handler for 'touchstart' event
	*
	* @param event Touch event
	*/
	onTouchStart = (event) => {
		const { clientX, clientY } = event.targetTouches ? event.targetTouches[0] : event;
		this.touchStart.x = clientX;
		this.touchStart.y = clientY;
		this.lastDelta = {
			x: 0,
			y: 0
		};
		this.emitter.emit("scroll", {
			deltaX: 0,
			deltaY: 0,
			event
		});
	};
	/** Event handler for 'touchmove' event */
	onTouchMove = (event) => {
		const { clientX, clientY } = event.targetTouches ? event.targetTouches[0] : event;
		const deltaX = -(clientX - this.touchStart.x) * this.options.touchMultiplier;
		const deltaY = -(clientY - this.touchStart.y) * this.options.touchMultiplier;
		this.touchStart.x = clientX;
		this.touchStart.y = clientY;
		this.lastDelta = {
			x: deltaX,
			y: deltaY
		};
		this.emitter.emit("scroll", {
			deltaX,
			deltaY,
			event
		});
	};
	onTouchEnd = (event) => {
		this.emitter.emit("scroll", {
			deltaX: this.lastDelta.x,
			deltaY: this.lastDelta.y,
			event
		});
	};
	/** Event handler for 'wheel' event */
	onWheel = (event) => {
		let { deltaX, deltaY, deltaMode } = event;
		const multiplierX = getDeltaMultiplier(deltaMode, this.window.width);
		const multiplierY = getDeltaMultiplier(deltaMode, this.window.height);
		deltaX *= multiplierX;
		deltaY *= multiplierY;
		deltaX *= this.options.wheelMultiplier;
		deltaY *= this.options.wheelMultiplier;
		this.emitter.emit("scroll", {
			deltaX,
			deltaY,
			event
		});
	};
	onWindowResize = () => {
		this.window = {
			width: window.innerWidth,
			height: window.innerHeight
		};
	};
};
var defaultEasing = (t) => Math.min(1, 1.001 - 2 ** (-10 * t));
var Lenis = class {
	_isScrolling = false;
	_isStopped = false;
	_isLocked = false;
	_preventNextNativeScrollEvent = false;
	_resetVelocityTimeout = null;
	_rafId = null;
	_isDraggingSelection = false;
	reducedMotionMediaQuery = window.matchMedia("(prefers-reduced-motion: reduce)");
	/**
	* Whether or not the user is touching the screen
	*/
	isTouching;
	/**
	* Whether or not the device is running iOS
	*/
	isIos;
	/**
	* The time in ms since the lenis instance was created
	*/
	time = 0;
	/**
	* User data that will be forwarded through the scroll event
	*
	* @example
	* lenis.scrollTo(100, {
	*   userData: {
	*     foo: 'bar'
	*   }
	* })
	*/
	userData = {};
	/**
	* The last velocity of the scroll
	*/
	lastVelocity = 0;
	/**
	* The current velocity of the scroll
	*/
	velocity = 0;
	/**
	* The direction of the scroll
	*/
	direction = 0;
	/**
	* The options passed to the lenis instance
	*/
	options;
	/**
	* The target scroll value
	*/
	targetScroll;
	/**
	* The animated scroll value
	*/
	animatedScroll;
	animate = new Animate();
	emitter = new Emitter();
	dimensions;
	virtualScroll;
	constructor({ wrapper = window, content = document.documentElement, eventsTarget = wrapper, smoothWheel = true, syncTouch = false, syncTouchLerp = .075, touchInertiaExponent = 1.7, duration, easing, lerp = .1, infinite = false, orientation = "vertical", gestureOrientation = orientation === "horizontal" ? "both" : "vertical", touchMultiplier = 1, wheelMultiplier = 1, autoResize = true, prevent, virtualScroll, overscroll = true, autoRaf = false, anchors = false, autoToggle = false, allowNestedScroll = false, __experimental__naiveDimensions = false, naiveDimensions = __experimental__naiveDimensions, stopInertiaOnNavigate = false, respectReducedMotion = true } = {}) {
		window.lenisVersion = version;
		if (!window.lenis) window.lenis = {};
		window.lenis.version = version;
		if (orientation === "horizontal") window.lenis.horizontal = true;
		if (syncTouch === true) window.lenis.touch = true;
		this.isIos = /(iPad|iPhone|iPod)/g.test(navigator.userAgent);
		if (!wrapper || wrapper === document.documentElement) wrapper = window;
		if (typeof duration === "number" && typeof easing !== "function") easing = defaultEasing;
		else if (typeof easing === "function" && typeof duration !== "number") duration = 1;
		this.options = {
			wrapper,
			content,
			eventsTarget,
			smoothWheel,
			syncTouch,
			syncTouchLerp,
			touchInertiaExponent,
			duration,
			easing,
			lerp,
			infinite,
			gestureOrientation,
			orientation,
			touchMultiplier,
			wheelMultiplier,
			autoResize,
			prevent,
			virtualScroll,
			overscroll,
			autoRaf,
			anchors,
			autoToggle,
			allowNestedScroll,
			naiveDimensions,
			stopInertiaOnNavigate,
			respectReducedMotion
		};
		this.dimensions = new Dimensions(wrapper, content, { autoResize });
		this.updateClassName();
		this.targetScroll = this.animatedScroll = this.actualScroll;
		this.options.wrapper.addEventListener("scroll", this.onNativeScroll);
		this.options.wrapper.addEventListener("scrollend", this.onScrollEnd, { capture: true });
		if (this.options.anchors || this.options.stopInertiaOnNavigate) this.options.wrapper.addEventListener("click", this.onClick);
		this.options.wrapper.addEventListener("pointerdown", this.onPointerDown);
		this.virtualScroll = new VirtualScroll(eventsTarget, {
			touchMultiplier,
			wheelMultiplier
		});
		this.virtualScroll.on("scroll", this.onVirtualScroll);
		if (this.options.autoToggle) {
			this.checkOverflow();
			this.rootElement.addEventListener("transitionend", this.onTransitionEnd);
		}
		if (this.options.autoRaf) this._rafId = requestAnimationFrame(this.raf);
	}
	/**
	* Destroy the lenis instance, remove all event listeners and clean up the class name
	*/
	destroy() {
		this.emitter.destroy();
		this.options.wrapper.removeEventListener("scroll", this.onNativeScroll);
		this.options.wrapper.removeEventListener("scrollend", this.onScrollEnd, { capture: true });
		this.options.wrapper.removeEventListener("pointerdown", this.onPointerDown);
		if (this.options.anchors || this.options.stopInertiaOnNavigate) this.options.wrapper.removeEventListener("click", this.onClick);
		this.virtualScroll.destroy();
		this.dimensions.destroy();
		this.cleanUpClassName();
		if (this._rafId) cancelAnimationFrame(this._rafId);
	}
	on(event, callback) {
		return this.emitter.on(event, callback);
	}
	off(event, callback) {
		return this.emitter.off(event, callback);
	}
	onScrollEnd = (e) => {
		if (!(e instanceof CustomEvent)) {
			if (this.isScrolling === "smooth" || this.isScrolling === false) e.stopPropagation();
		}
	};
	dispatchScrollendEvent = () => {
		this.options.wrapper.dispatchEvent(new CustomEvent("scrollend", {
			bubbles: this.options.wrapper === window,
			detail: { lenisScrollEnd: true }
		}));
	};
	get overflow() {
		const property = this.isHorizontal ? "overflow-x" : "overflow-y";
		return getComputedStyle(this.rootElement)[property];
	}
	checkOverflow() {
		if (["hidden", "clip"].includes(this.overflow)) this.internalStop();
		else this.internalStart();
	}
	onTransitionEnd = (event) => {
		if (event.propertyName?.includes("overflow") && event.target === this.rootElement) this.checkOverflow();
	};
	setScroll(scroll) {
		if (this.isHorizontal) this.options.wrapper.scrollTo({
			left: scroll,
			behavior: "instant"
		});
		else this.options.wrapper.scrollTo({
			top: scroll,
			behavior: "instant"
		});
	}
	onClick = (event) => {
		const linkElementsUrls = event.composedPath().filter((node) => node instanceof HTMLAnchorElement && node.href).map((element) => new URL(element.href));
		const currentUrl = new URL(window.location.href);
		if (this.options.anchors) {
			const anchorElementUrl = linkElementsUrls.find((targetUrl) => currentUrl.host === targetUrl.host && currentUrl.pathname === targetUrl.pathname && targetUrl.hash);
			if (anchorElementUrl) {
				const options = typeof this.options.anchors === "object" && this.options.anchors ? this.options.anchors : void 0;
				const target = decodeURIComponent(anchorElementUrl.hash);
				this.scrollTo(target, options);
				return;
			}
		}
		if (this.options.stopInertiaOnNavigate) {
			if (linkElementsUrls.some((targetUrl) => currentUrl.host === targetUrl.host && currentUrl.pathname !== targetUrl.pathname)) {
				this.reset();
				return;
			}
		}
	};
	onPointerDown = (event) => {
		if (event.button === 1) this.reset();
	};
	isTouchOnSelectionHandle(event) {
		const selection = window.getSelection();
		if (!selection || selection.isCollapsed || selection.rangeCount === 0) return false;
		const touch = event.targetTouches[0] ?? event.changedTouches[0];
		if (!touch) return false;
		const rects = selection.getRangeAt(0).getClientRects();
		if (rects.length === 0) return false;
		const first = rects[0];
		const last = rects[rects.length - 1];
		const HANDLE_RADIUS = 40;
		const nearStart = Math.hypot(touch.clientX - first.left, touch.clientY - first.top) <= HANDLE_RADIUS;
		const nearEnd = Math.hypot(touch.clientX - last.right, touch.clientY - last.bottom) <= HANDLE_RADIUS;
		return nearStart || nearEnd;
	}
	onVirtualScroll = (data) => {
		if (typeof this.options.virtualScroll === "function" && this.options.virtualScroll(data) === false) return;
		const { deltaX, deltaY, event } = data;
		this.emitter.emit("virtual-scroll", {
			deltaX,
			deltaY,
			event
		});
		if (event.ctrlKey) return;
		if (event.lenisStopPropagation) return;
		const isTouch = event.type.includes("touch");
		const isWheel = event.type.includes("wheel");
		if (isTouch && this.isIos) {
			if (event.type === "touchstart") this._isDraggingSelection = this.isTouchOnSelectionHandle(event);
			if (this._isDraggingSelection) {
				if (event.type === "touchend") this._isDraggingSelection = false;
				return;
			}
		}
		this.isTouching = event.type === "touchstart" || event.type === "touchmove";
		const isClickOrTap = deltaX === 0 && deltaY === 0;
		if (this.options.syncTouch && isTouch && event.type === "touchstart" && isClickOrTap && !this.isStopped && !this.isLocked) {
			this.reset();
			return;
		}
		const isUnknownGesture = this.options.gestureOrientation === "vertical" && deltaY === 0 || this.options.gestureOrientation === "horizontal" && deltaX === 0;
		if (isClickOrTap || isUnknownGesture) return;
		let composedPath = event.composedPath();
		composedPath = composedPath.slice(0, composedPath.indexOf(this.rootElement));
		const prevent = this.options.prevent;
		const gestureOrientation = Math.abs(deltaX) >= Math.abs(deltaY) ? "horizontal" : "vertical";
		if (composedPath.find((node) => node instanceof HTMLElement && (typeof prevent === "function" && prevent?.(node) || node.hasAttribute?.("data-lenis-prevent") || gestureOrientation === "vertical" && node.hasAttribute?.("data-lenis-prevent-vertical") || gestureOrientation === "horizontal" && node.hasAttribute?.("data-lenis-prevent-horizontal") || isTouch && node.hasAttribute?.("data-lenis-prevent-touch") || isWheel && node.hasAttribute?.("data-lenis-prevent-wheel") || this.options.allowNestedScroll && this.hasNestedScroll(node, {
			deltaX,
			deltaY
		})))) return;
		if (this.isStopped || this.isLocked) {
			if (event.cancelable) event.preventDefault();
			return;
		}
		if (!(this.options.syncTouch && isTouch || this.options.smoothWheel && isWheel)) {
			this.isScrolling = "native";
			this.animate.stop();
			event.lenisStopPropagation = true;
			return;
		}
		let delta = deltaY;
		if (this.options.gestureOrientation === "both") delta = Math.abs(deltaY) > Math.abs(deltaX) ? deltaY : deltaX;
		else if (this.options.gestureOrientation === "horizontal") delta = deltaX;
		if (!this.options.overscroll || this.options.infinite || this.options.wrapper !== window && this.limit > 0 && (this.animatedScroll > 0 && this.animatedScroll < this.limit || this.animatedScroll === 0 && deltaY > 0 || this.animatedScroll === this.limit && deltaY < 0)) event.lenisStopPropagation = true;
		if (event.cancelable) event.preventDefault();
		const isSyncTouch = isTouch && this.options.syncTouch;
		const hasTouchInertia = isTouch && event.type === "touchend";
		if (hasTouchInertia) delta = Math.sign(delta) * Math.abs(this.velocity) ** this.options.touchInertiaExponent;
		this.scrollTo(this.targetScroll + delta, {
			programmatic: false,
			...isSyncTouch ? { lerp: hasTouchInertia ? this.options.syncTouchLerp : 1 } : {
				lerp: this.options.lerp,
				duration: this.options.duration,
				easing: this.options.easing
			}
		});
	};
	/**
	* Force lenis to recalculate the dimensions
	*/
	resize() {
		this.dimensions.resize();
		this.animatedScroll = this.targetScroll = this.actualScroll;
		this.emit();
	}
	emit() {
		this.emitter.emit("scroll", this);
	}
	onNativeScroll = () => {
		if (this._resetVelocityTimeout !== null) {
			clearTimeout(this._resetVelocityTimeout);
			this._resetVelocityTimeout = null;
		}
		if (this._preventNextNativeScrollEvent) {
			this._preventNextNativeScrollEvent = false;
			return;
		}
		if (this.isScrolling === false || this.isScrolling === "native") {
			const lastScroll = this.animatedScroll;
			this.animatedScroll = this.targetScroll = this.actualScroll;
			this.lastVelocity = this.velocity;
			this.velocity = this.animatedScroll - lastScroll;
			this.direction = Math.sign(this.animatedScroll - lastScroll);
			if (!this.isStopped) this.isScrolling = "native";
			this.emit();
			if (this.velocity !== 0) this._resetVelocityTimeout = setTimeout(() => {
				this.lastVelocity = this.velocity;
				this.velocity = 0;
				this.isScrolling = false;
				this.emit();
			}, 400);
		}
	};
	reset() {
		this.isLocked = false;
		this.isScrolling = false;
		this.animatedScroll = this.targetScroll = this.actualScroll;
		this.lastVelocity = this.velocity = 0;
		this.animate.stop();
	}
	/**
	* Start lenis scroll after it has been stopped
	*/
	start() {
		if (!this.isStopped) return;
		if (this.options.autoToggle) {
			this.rootElement.style.removeProperty("overflow");
			return;
		}
		this.internalStart();
	}
	internalStart() {
		if (!this.isStopped) return;
		this.reset();
		this.isStopped = false;
		this.emit();
	}
	/**
	* Stop lenis scroll
	*/
	stop() {
		if (this.isStopped) return;
		if (this.options.autoToggle) {
			this.rootElement.style.setProperty("overflow", "clip");
			return;
		}
		this.internalStop();
	}
	internalStop() {
		if (this.isStopped) return;
		this.reset();
		this.isStopped = true;
		this.emit();
	}
	/**
	* RequestAnimationFrame for lenis
	*
	* @param time The time in ms from an external clock like `requestAnimationFrame` or Tempus
	*/
	raf = (time) => {
		const deltaTime = time - (this.time || time);
		this.time = time;
		this.animate.advance(deltaTime * .001);
		if (this.options.autoRaf) this._rafId = requestAnimationFrame(this.raf);
	};
	/**
	* Scroll to a target value
	*
	* @param target The target value to scroll to
	* @param options The options for the scroll
	*
	* @example
	* lenis.scrollTo(100, {
	*   offset: 100,
	*   duration: 1,
	*   easing: (t) => 1 - Math.cos((t * Math.PI) / 2),
	*   lerp: 0.1,
	*   onStart: () => {
	*     console.log('onStart')
	*   },
	*   onComplete: () => {
	*     console.log('onComplete')
	*   },
	* })
	*/
	scrollTo(_target, { offset = 0, immediate = false, lock = false, programmatic = true, lerp = programmatic ? this.options.lerp : void 0, duration = programmatic ? this.options.duration : void 0, easing = programmatic ? this.options.easing : void 0, onStart, onComplete, force = false, userData } = {}) {
		if (this.prefersReducedMotion) if (programmatic) immediate = true;
		else {
			lerp = 1;
			duration = void 0;
			easing = void 0;
		}
		if ((this.isStopped || this.isLocked) && !force) return;
		let target = _target;
		let adjustedOffset = offset;
		if (typeof target === "string" && [
			"top",
			"left",
			"start",
			"#"
		].includes(target)) target = 0;
		else if (typeof target === "string" && [
			"bottom",
			"right",
			"end"
		].includes(target)) target = this.limit;
		else {
			let node = null;
			if (typeof target === "string") {
				node = target.startsWith("#") ? document.getElementById(target.slice(1)) : document.querySelector(target);
				if (!node) if (target === "#top") target = 0;
				else console.warn("Lenis: Target not found", target);
			} else if (target instanceof HTMLElement && target?.nodeType) node = target;
			if (node) {
				if (this.options.wrapper !== window) {
					const wrapperRect = this.rootElement.getBoundingClientRect();
					adjustedOffset -= this.isHorizontal ? wrapperRect.left : wrapperRect.top;
				}
				const rect = node.getBoundingClientRect();
				const targetStyle = getComputedStyle(node);
				const scrollMargin = this.isHorizontal ? Number.parseFloat(targetStyle.scrollMarginLeft) : Number.parseFloat(targetStyle.scrollMarginTop);
				const containerStyle = getComputedStyle(this.rootElement);
				const scrollPadding = this.isHorizontal ? Number.parseFloat(containerStyle.scrollPaddingLeft) : Number.parseFloat(containerStyle.scrollPaddingTop);
				target = (this.isHorizontal ? rect.left : rect.top) + this.animatedScroll - (Number.isNaN(scrollMargin) ? 0 : scrollMargin) - (Number.isNaN(scrollPadding) ? 0 : scrollPadding);
			}
		}
		if (typeof target !== "number") return;
		target += adjustedOffset;
		if (this.options.infinite) {
			if (programmatic) {
				this.targetScroll = this.animatedScroll = this.scroll;
				const distance = target - this.animatedScroll;
				if (distance > this.limit / 2) target -= this.limit;
				else if (distance < -this.limit / 2) target += this.limit;
			}
		} else target = clamp(0, target, this.limit);
		if (target === this.targetScroll) {
			onStart?.(this);
			onComplete?.(this);
			return;
		}
		this.userData = userData ?? {};
		if (immediate) {
			this.animatedScroll = this.targetScroll = target;
			this.setScroll(this.scroll);
			this.reset();
			this.preventNextNativeScrollEvent();
			this.emit();
			onComplete?.(this);
			this.userData = {};
			requestAnimationFrame(() => {
				this.dispatchScrollendEvent();
			});
			return;
		}
		if (!programmatic) this.targetScroll = target;
		if (typeof duration === "number" && typeof easing !== "function") easing = defaultEasing;
		else if (typeof easing === "function" && typeof duration !== "number") duration = 1;
		this.animate.fromTo(this.animatedScroll, target, {
			duration,
			easing,
			lerp,
			onStart: () => {
				if (lock) this.isLocked = true;
				this.isScrolling = "smooth";
				onStart?.(this);
			},
			onUpdate: (value, completed) => {
				this.isScrolling = "smooth";
				this.lastVelocity = this.velocity;
				this.velocity = value - this.animatedScroll;
				this.direction = Math.sign(this.velocity);
				this.animatedScroll = value;
				this.setScroll(this.scroll);
				if (programmatic) this.targetScroll = value;
				if (!completed) this.emit();
				if (completed) {
					this.reset();
					this.emit();
					onComplete?.(this);
					this.userData = {};
					requestAnimationFrame(() => {
						this.dispatchScrollendEvent();
					});
					this.preventNextNativeScrollEvent();
				}
			}
		});
	}
	preventNextNativeScrollEvent() {
		this._preventNextNativeScrollEvent = true;
		requestAnimationFrame(() => {
			this._preventNextNativeScrollEvent = false;
		});
	}
	hasNestedScroll(node, { deltaX, deltaY }) {
		const time = Date.now();
		if (!node._lenis) node._lenis = {};
		const cache = node._lenis;
		let hasOverflowX;
		let hasOverflowY;
		let isScrollableX;
		let isScrollableY;
		let hasOverscrollBehaviorX;
		let hasOverscrollBehaviorY;
		let scrollWidth;
		let scrollHeight;
		let clientWidth;
		let clientHeight;
		if (time - (cache.time ?? 0) > 2e3) {
			cache.time = Date.now();
			const computedStyle = window.getComputedStyle(node);
			cache.computedStyle = computedStyle;
			hasOverflowX = [
				"auto",
				"overlay",
				"scroll"
			].includes(computedStyle.overflowX);
			hasOverflowY = [
				"auto",
				"overlay",
				"scroll"
			].includes(computedStyle.overflowY);
			hasOverscrollBehaviorX = ["auto"].includes(computedStyle.overscrollBehaviorX);
			hasOverscrollBehaviorY = ["auto"].includes(computedStyle.overscrollBehaviorY);
			cache.hasOverflowX = hasOverflowX;
			cache.hasOverflowY = hasOverflowY;
			if (!(hasOverflowX || hasOverflowY)) return false;
			scrollWidth = node.scrollWidth;
			scrollHeight = node.scrollHeight;
			clientWidth = node.clientWidth;
			clientHeight = node.clientHeight;
			isScrollableX = scrollWidth > clientWidth;
			isScrollableY = scrollHeight > clientHeight;
			cache.isScrollableX = isScrollableX;
			cache.isScrollableY = isScrollableY;
			cache.scrollWidth = scrollWidth;
			cache.scrollHeight = scrollHeight;
			cache.clientWidth = clientWidth;
			cache.clientHeight = clientHeight;
			cache.hasOverscrollBehaviorX = hasOverscrollBehaviorX;
			cache.hasOverscrollBehaviorY = hasOverscrollBehaviorY;
		} else {
			isScrollableX = cache.isScrollableX;
			isScrollableY = cache.isScrollableY;
			hasOverflowX = cache.hasOverflowX;
			hasOverflowY = cache.hasOverflowY;
			scrollWidth = cache.scrollWidth;
			scrollHeight = cache.scrollHeight;
			clientWidth = cache.clientWidth;
			clientHeight = cache.clientHeight;
			hasOverscrollBehaviorX = cache.hasOverscrollBehaviorX;
			hasOverscrollBehaviorY = cache.hasOverscrollBehaviorY;
		}
		if (!(hasOverflowX && isScrollableX || hasOverflowY && isScrollableY)) return false;
		const orientation = Math.abs(deltaX) >= Math.abs(deltaY) ? "horizontal" : "vertical";
		let scroll;
		let maxScroll;
		let delta;
		let hasOverflow;
		let isScrollable;
		let hasOverscrollBehavior;
		if (orientation === "horizontal") {
			scroll = Math.round(node.scrollLeft);
			maxScroll = scrollWidth - clientWidth;
			delta = deltaX;
			hasOverflow = hasOverflowX;
			isScrollable = isScrollableX;
			hasOverscrollBehavior = hasOverscrollBehaviorX;
		} else if (orientation === "vertical") {
			scroll = Math.round(node.scrollTop);
			maxScroll = scrollHeight - clientHeight;
			delta = deltaY;
			hasOverflow = hasOverflowY;
			isScrollable = isScrollableY;
			hasOverscrollBehavior = hasOverscrollBehaviorY;
		} else return false;
		if (!hasOverscrollBehavior && (scroll >= maxScroll || scroll <= 0)) return true;
		return (delta > 0 ? scroll < maxScroll : scroll > 0) && hasOverflow && isScrollable;
	}
	/**
	* The root element on which lenis is instanced
	*/
	get rootElement() {
		return this.options.wrapper === window ? document.documentElement : this.options.wrapper;
	}
	/**
	* The limit which is the maximum scroll value
	*/
	get limit() {
		if (this.options.naiveDimensions) {
			if (this.isHorizontal) return this.rootElement.scrollWidth - this.rootElement.clientWidth;
			return this.rootElement.scrollHeight - this.rootElement.clientHeight;
		}
		return this.dimensions.limit[this.isHorizontal ? "x" : "y"];
	}
	/**
	* Whether or not the scroll is horizontal
	*/
	get isHorizontal() {
		return this.options.orientation === "horizontal";
	}
	/**
	* The actual scroll value
	*/
	get actualScroll() {
		const wrapper = this.options.wrapper;
		return this.isHorizontal ? wrapper.scrollX ?? wrapper.scrollLeft : wrapper.scrollY ?? wrapper.scrollTop;
	}
	/**
	* The current scroll value
	*/
	get scroll() {
		return this.options.infinite ? modulo(this.animatedScroll, this.limit) : this.animatedScroll;
	}
	/**
	* The progress of the scroll relative to the limit
	*/
	get progress() {
		return this.limit === 0 ? 1 : this.scroll / this.limit;
	}
	/**
	* Current scroll state
	*/
	get isScrolling() {
		return this._isScrolling;
	}
	set isScrolling(value) {
		if (this._isScrolling !== value) {
			this._isScrolling = value;
			this.updateClassName();
		}
	}
	/**
	* Check if lenis is stopped
	*/
	get isStopped() {
		return this._isStopped;
	}
	set isStopped(value) {
		if (this._isStopped !== value) {
			this._isStopped = value;
			this.updateClassName();
		}
	}
	/**
	* Check if lenis is locked
	*/
	get isLocked() {
		return this._isLocked;
	}
	set isLocked(value) {
		if (this._isLocked !== value) {
			this._isLocked = value;
			this.updateClassName();
		}
	}
	/**
	* Check if lenis is smooth scrolling
	*/
	get isSmooth() {
		return this.isScrolling === "smooth";
	}
	/**
	* Whether the user prefers reduced motion and lenis is honoring it (see `respectReducedMotion` option)
	*/
	get prefersReducedMotion() {
		return this.options.respectReducedMotion && this.reducedMotionMediaQuery.matches;
	}
	/**
	* The class name applied to the wrapper element
	*/
	get className() {
		let className = "lenis";
		if (this.options.autoToggle) className += " lenis-autoToggle";
		if (this.isStopped) className += " lenis-stopped";
		if (this.isLocked) className += " lenis-locked";
		if (this.isScrolling) className += " lenis-scrolling";
		if (this.isScrolling === "smooth") className += " lenis-smooth";
		return className;
	}
	updateClassName() {
		this.cleanUpClassName();
		this.className.split(" ").forEach((className) => {
			this.rootElement.classList.add(className);
		});
	}
	cleanUpClassName() {
		for (const className of Array.from(this.rootElement.classList)) if (className === "lenis" || className.startsWith("lenis-")) this.rootElement.classList.remove(className);
	}
};
//#endregion
//#region resources/js/Components/Layout/SmoothScroll.tsx
/**
* Inertial scrolling on mouse/trackpad only. Touch keeps native scrolling and
* reduced motion keeps the browser default. Resets to the top on page visits.
*/
function SmoothScroll() {
	useEffect(() => {
		const fine = window.matchMedia("(hover: hover) and (pointer: fine)").matches;
		const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
		if (!fine || reduced) return;
		const lenis = new Lenis({
			lerp: .11,
			wheelMultiplier: .95,
			anchors: true
		});
		let raf = requestAnimationFrame(function frame(time) {
			lenis.raf(time);
			raf = requestAnimationFrame(frame);
		});
		const off = router.on("navigate", () => lenis.scrollTo(0, { immediate: true }));
		return () => {
			off();
			cancelAnimationFrame(raf);
			lenis.destroy();
		};
	}, []);
	return null;
}
//#endregion
//#region resources/js/Layouts/PublicLayout.tsx
function FlashToasts() {
	const { flash } = usePage().props;
	const show = useToast();
	useEffect(() => {
		if (flash?.toast) show(flash.toast);
	}, [flash, show]);
	return null;
}
function PublicLayout({ children }) {
	return /* @__PURE__ */ jsxs(ToastProvider, { children: [
		/* @__PURE__ */ jsx("a", {
			href: "#conteudo",
			className: "fixed top-3 left-3 z-[80] -translate-y-[200%] rounded-full bg-beam px-5 py-3 font-semibold text-night transition-transform duration-200 ease-snap focus-visible:translate-y-0",
			children: t.nav.skip
		}),
		/* @__PURE__ */ jsx(SmoothScroll, {}),
		/* @__PURE__ */ jsx(FlashToasts, {}),
		/* @__PURE__ */ jsx(Header, {}),
		/* @__PURE__ */ jsx("main", {
			id: "conteudo",
			tabIndex: -1,
			className: "outline-none",
			children
		}),
		/* @__PURE__ */ jsx(Footer, {})
	] });
}
//#endregion
export { SeoHead as n, PublicLayout as t };
