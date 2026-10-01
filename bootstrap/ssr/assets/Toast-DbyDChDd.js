import { c as SaucerShape, f as t, i as Starfield, l as YellowCarShape } from "./Typography-BT6DGpEB.js";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import { createContext, useCallback, useContext, useEffect, useId, useRef, useState } from "react";
import * as fm from "framer-motion";
//#region \0rolldown/runtime.js
var __defProp = Object.defineProperty;
var __getOwnPropDesc = Object.getOwnPropertyDescriptor;
var __getOwnPropNames = Object.getOwnPropertyNames;
var __hasOwnProp = Object.prototype.hasOwnProperty;
var __exportAll = (all, no_symbols) => {
	let target = {};
	for (var name in all) __defProp(target, name, {
		get: all[name],
		enumerable: true
	});
	if (!no_symbols) __defProp(target, Symbol.toStringTag, { value: "Module" });
	return target;
};
var __copyProps = (to, from, except, desc) => {
	if (from && typeof from === "object" || typeof from === "function") for (var keys = __getOwnPropNames(from), i = 0, n = keys.length, key; i < n; i++) {
		key = keys[i];
		if (!__hasOwnProp.call(to, key) && key !== except) __defProp(to, key, {
			get: ((k) => from[k]).bind(null, key),
			enumerable: !(desc = __getOwnPropDesc(from, key)) || desc.enumerable
		});
	}
	return to;
};
var __reExport = (target, mod, secondTarget) => (__copyProps(target, mod, "default"), secondTarget && __copyProps(secondTarget, mod, "default"));
//#endregion
//#region resources/js/Components/Brand/Seal.tsx
var sizes = {
	sm: "size-12",
	md: "size-40",
	lg: "size-[clamp(13rem,9rem+18vw,22rem)]"
};
/**
* Provisional seal (the printed sticker), drawn in code until the real artwork
* lands in public/brand/seal.svg. Text sits on circular paths in the brand fonts.
*/
function SealArt({ title = "Selo OVNIPORTO · Lages SC", simplified = false }) {
	const uid = useId().replace(/:/g, "");
	const top = `seal-top-${uid}`;
	const bottom = `seal-bottom-${uid}`;
	const sky = `seal-sky-${uid}`;
	const beam = `seal-beam-${uid}`;
	const clip = `seal-clip-${uid}`;
	return /* @__PURE__ */ jsxs("svg", {
		viewBox: "0 0 240 240",
		role: "img",
		"aria-label": title,
		className: "h-full w-full",
		children: [
			/* @__PURE__ */ jsxs("defs", { children: [
				/* @__PURE__ */ jsx("path", {
					id: top,
					d: "M 30 120 A 90 90 0 0 1 210 120"
				}),
				/* @__PURE__ */ jsx("path", {
					id: bottom,
					d: "M 22 120 A 98 98 0 0 0 218 120"
				}),
				/* @__PURE__ */ jsx("clipPath", {
					id: clip,
					children: /* @__PURE__ */ jsx("circle", {
						cx: "120",
						cy: "120",
						r: "65.5"
					})
				}),
				/* @__PURE__ */ jsxs("radialGradient", {
					id: sky,
					cx: "50%",
					cy: "20%",
					r: "80%",
					children: [/* @__PURE__ */ jsx("stop", {
						offset: "0%",
						stopColor: "var(--color-night-blue)"
					}), /* @__PURE__ */ jsx("stop", {
						offset: "100%",
						stopColor: "var(--color-night)"
					})]
				}),
				/* @__PURE__ */ jsxs("linearGradient", {
					id: beam,
					x1: "0",
					y1: "0",
					x2: "0",
					y2: "1",
					children: [/* @__PURE__ */ jsx("stop", {
						offset: "0%",
						stopColor: "var(--color-beam)",
						stopOpacity: "0.85"
					}), /* @__PURE__ */ jsx("stop", {
						offset: "100%",
						stopColor: "var(--color-beam)",
						stopOpacity: "0.1"
					})]
				})
			] }),
			/* @__PURE__ */ jsx("circle", {
				cx: "120",
				cy: "120",
				r: "119",
				fill: "var(--color-night)"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "120",
				cy: "120",
				r: "113",
				fill: "none",
				stroke: "var(--color-beam)",
				strokeWidth: "3.5"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "120",
				cy: "120",
				r: "66",
				fill: `url(#${sky})`,
				stroke: "var(--color-moonlight)",
				strokeOpacity: "0.25"
			}),
			!simplified && /* @__PURE__ */ jsxs(Fragment, { children: [
				/* @__PURE__ */ jsx("text", {
					fill: "var(--color-moonlight)",
					fontFamily: "var(--font-display)",
					fontWeight: "800",
					fontSize: "25",
					letterSpacing: "2.4",
					children: /* @__PURE__ */ jsx("textPath", {
						href: `#${top}`,
						startOffset: "50%",
						textAnchor: "middle",
						children: "OVNIPORTO"
					})
				}),
				/* @__PURE__ */ jsx("text", {
					fill: "var(--color-beam-glow)",
					fontFamily: "var(--font-sans)",
					fontWeight: "600",
					fontSize: "11.5",
					letterSpacing: "3.2",
					children: /* @__PURE__ */ jsx("textPath", {
						href: `#${bottom}`,
						startOffset: "50%",
						textAnchor: "middle",
						dominantBaseline: "hanging",
						children: "LAGES · SANTA CATARINA"
					})
				}),
				/* @__PURE__ */ jsx("path", {
					d: "M22 120 l3 -6 3 6 -3 6 Z M212 120 l3 -6 3 6 -3 6 Z",
					fill: "var(--color-car)"
				})
			] }),
			/* @__PURE__ */ jsxs("g", {
				clipPath: `url(#${clip})`,
				children: [
					[
						[
							88,
							82,
							1.2
						],
						[
							150,
							76,
							1
						],
						[
							160,
							100,
							1.4
						],
						[
							80,
							108,
							.9
						],
						[
							128,
							68,
							.8
						]
					].map(([x, y, r]) => /* @__PURE__ */ jsx("circle", {
						cx: x,
						cy: y,
						r,
						fill: "var(--color-moonlight)",
						opacity: "0.8"
					}, `${x}-${y}`)),
					/* @__PURE__ */ jsx("path", {
						d: "M110 100 L130 100 L146 156 L94 156 Z",
						fill: `url(#${beam})`
					}),
					/* @__PURE__ */ jsx("g", {
						transform: "translate(120 96) scale(0.24)",
						children: /* @__PURE__ */ jsx(SaucerShape, {})
					}),
					/* @__PURE__ */ jsx("path", {
						d: "M58 158 C90 152 150 152 182 158 L182 186 L58 186 Z",
						fill: "var(--color-night)"
					}),
					/* @__PURE__ */ jsx("g", {
						transform: "translate(120 160) scale(0.24)",
						children: /* @__PURE__ */ jsx(YellowCarShape, {})
					})
				]
			})
		]
	});
}
function Seal({ size = "md", glow = false, className = "" }) {
	return /* @__PURE__ */ jsxs("span", {
		className: `relative inline-block ${sizes[size]} ${className}`,
		children: [glow && /* @__PURE__ */ jsx("span", {
			"aria-hidden": true,
			className: "absolute -inset-[9%] animate-seal-glow rounded-full bg-[radial-gradient(closest-side,rgb(84_201_51/0.55),rgb(84_201_51/0.18)_55%,transparent_72%)] motion-reduce:animate-none"
		}), /* @__PURE__ */ jsx("span", {
			className: "relative block h-full w-full rounded-full shadow-[0_18px_50px_-18px_rgb(6_17_33/0.8)]",
			children: /* @__PURE__ */ jsx(SealArt, { simplified: size === "sm" })
		})]
	});
}
//#endregion
//#region resources/js/Components/Ui/Section.tsx
var tones = {
	light: "bg-moonlight text-night",
	dusk: "bg-[color-mix(in_srgb,var(--color-night)_4%,var(--color-moonlight))] text-night",
	dark: "bg-night text-moonlight grain"
};
var waveFill = {
	light: "text-moonlight",
	dusk: "text-[color-mix(in_srgb,var(--color-night)_4%,var(--color-moonlight))]",
	dark: "text-night"
};
/**
* A page band. Dark bands get the starfield and report themselves to the header
* (data-tone) so the floating menu can invert. `wave` draws a soft hill-line edge
* over the previous band instead of a straight cut: the serra skyline, simplified.
*/
function Section({ tone = "light", pattern = "none", wave = false, id, labelledBy, className = "", innerClassName = "", children }) {
	const dark = tone === "dark";
	return /* @__PURE__ */ jsxs("section", {
		id,
		"aria-labelledby": labelledBy,
		"data-tone": dark ? "dark" : "light",
		className: `relative ${tones[tone]} ${className}`,
		children: [
			wave && /* @__PURE__ */ jsx("svg", {
				"aria-hidden": true,
				viewBox: "0 0 1440 56",
				preserveAspectRatio: "none",
				className: `pointer-events-none absolute inset-x-0 -top-[38px] z-[2] h-10 w-full ${waveFill[tone]}`,
				children: /* @__PURE__ */ jsx("path", {
					fill: "currentColor",
					d: "M0 56V34c96-14 168-26 262-16 92 10 150 26 252 22 108-4 170-34 284-34 112 0 168 30 278 30 104 0 168-24 260-26 46-1 80 4 104 8v38H0Z"
				})
			}),
			dark && pattern === "stars" && /* @__PURE__ */ jsx(Starfield, {
				className: "absolute inset-0 z-0",
				density: "low",
				parallax: true
			}),
			/* @__PURE__ */ jsx("div", {
				className: `relative z-[2] mx-auto w-full max-w-[84rem] px-5 py-[clamp(4rem,3rem+5vw,8rem)] sm:px-8 ${innerClassName}`,
				children
			})
		]
	});
}
//#endregion
//#region node_modules/motion/dist/es/react.mjs
var react_exports = /* @__PURE__ */ __exportAll({
	m: () => m,
	motion: () => motion
});
import * as import_framer_motion from "framer-motion";
__reExport(react_exports, import_framer_motion);
var motion = fm.motion;
var m = fm.m;
//#endregion
//#region resources/js/lib/motion.ts
/**
* Motion tokens shared by every animated component. Values follow the
* `animate` / `emil-design-eng` skills: strong custom curves, UI under 300ms.
*/
var ease = {
	/** Entrances, exits, anything answering the user. */
	snap: [
		.23,
		1,
		.32,
		1
	],
	/** Movement across the screen. */
	glide: [
		.77,
		0,
		.175,
		1
	],
	/** Drawers and full-screen panels. */
	drawer: [
		.32,
		.72,
		0,
		1
	]
};
/** Same curves as functions, for scroll-linked useTransform segments. */
var easeFn = {
	snap: (0, react_exports.cubicBezier)(...ease.snap),
	glide: (0, react_exports.cubicBezier)(...ease.glide),
	linear: (v) => v
};
var duration = {
	press: .14,
	ui: .22,
	panel: .42,
	reveal: .6
};
var spring = {
	/** Polaroids settling on the table. */
	paper: {
		type: "spring",
		duration: .6,
		bounce: .18
	},
	/** Hover straightening: interruptible, no overshoot. */
	soft: {
		type: "spring",
		duration: .45,
		bounce: 0
	}
};
/** Stagger between siblings (the brief asks for 80ms between polaroids). */
var STAGGER = .08;
//#endregion
//#region resources/js/Components/Ui/Toast.tsx
var ToastContext = createContext(() => void 0);
var useToast = () => useContext(ToastContext);
/** One toast at a time, bottom center, enters and leaves through the bottom edge. */
function ToastProvider({ children }) {
	const [toast, setToast] = useState(null);
	const timer = useRef(void 0);
	const show = useCallback((message) => {
		window.clearTimeout(timer.current);
		setToast({
			id: Date.now(),
			message
		});
		timer.current = window.setTimeout(() => setToast(null), 4200);
	}, []);
	useEffect(() => () => window.clearTimeout(timer.current), []);
	return /* @__PURE__ */ jsxs(ToastContext.Provider, {
		value: show,
		children: [children, /* @__PURE__ */ jsx("div", {
			role: "status",
			"aria-live": "polite",
			className: "pointer-events-none fixed inset-x-0 bottom-[max(1.25rem,env(safe-area-inset-bottom))] z-[70] flex justify-center px-4",
			children: /* @__PURE__ */ jsx(react_exports.AnimatePresence, { children: toast && /* @__PURE__ */ jsxs(motion.div, {
				initial: {
					opacity: 0,
					transform: "translateY(120%)"
				},
				animate: {
					opacity: 1,
					transform: "translateY(0%)"
				},
				exit: {
					opacity: 0,
					transform: "translateY(120%)"
				},
				transition: {
					duration: duration.ui + .08,
					ease: ease.snap
				},
				className: "pointer-events-auto flex items-center gap-3 rounded-full bg-night-blue py-2 pr-2 pl-5 text-moonlight shadow-[0_18px_40px_-16px_rgb(6_17_33/0.8)] ring-1 ring-beam/40",
				children: [
					/* @__PURE__ */ jsx("span", {
						"aria-hidden": true,
						className: "size-2 rounded-full bg-beam shadow-[0_0_10px_var(--color-beam)]"
					}),
					/* @__PURE__ */ jsx("span", {
						className: "text-[0.95rem] font-medium",
						children: toast.message
					}),
					/* @__PURE__ */ jsx("button", {
						type: "button",
						onClick: () => setToast(null),
						"aria-label": t.toast.close,
						className: "press inline-flex size-9 items-center justify-center rounded-full hover:bg-moonlight/10",
						children: /* @__PURE__ */ jsx("svg", {
							viewBox: "0 0 24 24",
							className: "size-4",
							fill: "none",
							"aria-hidden": true,
							children: /* @__PURE__ */ jsx("path", {
								d: "M6 6l12 12M18 6L6 18",
								stroke: "currentColor",
								strokeWidth: "2",
								strokeLinecap: "round"
							})
						})
					})
				]
			}, toast.id) })
		})]
	});
}
//#endregion
export { ease as a, motion as c, Seal as d, SealArt as f, duration as i, react_exports as l, useToast as n, easeFn as o, STAGGER as r, spring as s, ToastProvider as t, Section as u };
