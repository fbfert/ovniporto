import { c as motion, s as spring } from "./Toast-DbyDChDd.js";
import { Link } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import { useId } from "react";
//#region resources/js/Components/Ui/InfoCard.tsx
/** Short fact, like a field on a boarding pass: tiny tracked label above a value. */
function InfoCard({ label, value, href, extra }) {
	const inner = /* @__PURE__ */ jsxs(Fragment, { children: [/* @__PURE__ */ jsx("dt", {
		className: "text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase",
		children: label
	}), /* @__PURE__ */ jsxs("dd", {
		className: "mt-1.5 flex flex-wrap items-center gap-2 text-base leading-snug font-semibold",
		children: [value, extra]
	})] });
	return href ? /* @__PURE__ */ jsx(Link, {
		href,
		className: "block rounded-lg underline-offset-4 hover:underline",
		children: inner
	}) : /* @__PURE__ */ jsx("div", { children: inner });
}
//#endregion
//#region resources/js/Components/Ui/Marquee.tsx
var FLAG_COLORS = [
	"var(--color-car)",
	"var(--color-beam)",
	"var(--color-moonlight)",
	"var(--color-beam-glow)"
];
/** Festa-junina bunting hanging from a string: four alternating brand colors. */
function Bunting() {
	const id = `bunting-${useId().replace(/:/g, "")}`;
	return /* @__PURE__ */ jsxs("svg", {
		"aria-hidden": true,
		className: "absolute inset-x-0 -top-[18px] h-7 w-full",
		preserveAspectRatio: "none",
		children: [/* @__PURE__ */ jsx("defs", { children: /* @__PURE__ */ jsxs("pattern", {
			id,
			width: "152",
			height: "28",
			patternUnits: "userSpaceOnUse",
			children: [/* @__PURE__ */ jsx("path", {
				d: "M0 3 Q76 9 152 3",
				stroke: "var(--color-night)",
				strokeOpacity: "0.55",
				strokeWidth: "1.2",
				fill: "none"
			}), FLAG_COLORS.map((color, i) => /* @__PURE__ */ jsx("path", {
				d: `M${6 + i * 38} 4 L${32 + i * 38} 4.5 L${19 + i * 38} 26 Z`,
				fill: color
			}, i))]
		}) }), /* @__PURE__ */ jsx("rect", {
			width: "100%",
			height: "28",
			fill: `url(#${id})`
		})]
	});
}
function Saucer() {
	return /* @__PURE__ */ jsxs("svg", {
		"aria-hidden": true,
		viewBox: "0 0 40 20",
		className: "mx-6 h-5 w-10 shrink-0 text-beam-glow",
		children: [
			/* @__PURE__ */ jsx("path", {
				d: "M12 9 C13 2 27 2 28 9 Z",
				fill: "currentColor",
				opacity: "0.85"
			}),
			/* @__PURE__ */ jsx("ellipse", {
				cx: "20",
				cy: "11",
				rx: "19",
				ry: "5",
				fill: "currentColor"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "9",
				cy: "12",
				r: "1.3",
				fill: "var(--color-horizon)"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "20",
				cy: "13.5",
				r: "1.3",
				fill: "var(--color-horizon)"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "31",
				cy: "12",
				r: "1.3",
				fill: "var(--color-horizon)"
			})
		]
	});
}
/**
* Endless strip, slightly tilted and wider than the screen so its ends never
* show. Pauses on hover; becomes a static centered line with reduced motion.
*/
function Marquee({ items, speed = "normal", tilt = -1.5 }) {
	const run = /* @__PURE__ */ jsx("span", {
		className: "flex shrink-0 items-center",
		children: items.map((item) => /* @__PURE__ */ jsxs("span", {
			className: "flex items-center",
			children: [/* @__PURE__ */ jsx("span", {
				className: "font-display text-[clamp(1rem,0.8rem+1vw,1.5rem)] font-bold tracking-[0.06em] whitespace-nowrap uppercase",
				children: item
			}), /* @__PURE__ */ jsx(Saucer, {})]
		}, item))
	});
	return /* @__PURE__ */ jsx("div", {
		className: "relative z-[3] -my-3 overflow-x-clip py-8",
		"aria-label": items.join(" · "),
		role: "marquee",
		children: /* @__PURE__ */ jsxs("div", {
			className: "group/marquee relative -mx-[5vw] bg-horizon py-4 text-moonlight shadow-[0_20px_40px_-24px_rgb(6_17_33/0.6)]",
			style: { transform: `rotate(${tilt}deg)` },
			children: [
				/* @__PURE__ */ jsx(Bunting, {}),
				/* @__PURE__ */ jsxs("div", {
					"aria-hidden": true,
					className: "flex w-max animate-marquee group-hover/marquee:[animation-play-state:paused] motion-reduce:hidden",
					style: { ["--marquee-duration"]: speed === "slow" ? "60s" : "38s" },
					children: [
						run,
						run,
						run,
						run
					]
				}),
				/* @__PURE__ */ jsx("p", {
					className: "hidden px-6 text-center font-display font-bold tracking-[0.06em] uppercase motion-reduce:block",
					children: items.join(" ✦ ")
				})
			]
		})
	});
}
//#endregion
//#region resources/js/Components/Ui/Polaroid.tsx
/**
* Instant photo on the table: white-ish frame with a deeper bottom edge, a strip
* of tape and a slight tilt. Hover straightens it (pointer devices only: Motion
* ignores touch for hover gestures).
*/
function Polaroid({ src, alt = "", art, caption, rotate = -3, tape = "top", href, className = "", imageClassName = "aspect-[4/5]" }) {
	const body = /* @__PURE__ */ jsxs(motion.figure, {
		initial: false,
		style: { rotate },
		whileHover: {
			rotate: 0,
			y: -6,
			scale: 1.015
		},
		transition: spring.soft,
		className: `relative bg-moonlight p-3 pb-0 text-night shadow-polaroid ${className}`,
		children: [
			tape === "top" && /* @__PURE__ */ jsx("span", {
				"aria-hidden": true,
				className: "tape absolute -top-3 left-1/2 z-10 h-7 w-24 -translate-x-1/2 -rotate-3"
			}),
			tape === "corner" && /* @__PURE__ */ jsx("span", {
				"aria-hidden": true,
				className: "tape absolute -top-2 -right-5 z-10 h-6 w-20 rotate-[38deg]"
			}),
			/* @__PURE__ */ jsxs("div", {
				className: `relative overflow-hidden bg-night ${imageClassName}`,
				children: [src ? /* @__PURE__ */ jsx("img", {
					src,
					alt,
					loading: "lazy",
					decoding: "async",
					className: "h-full w-full object-cover"
				}) : art, /* @__PURE__ */ jsx("span", {
					"aria-hidden": true,
					className: "pointer-events-none absolute inset-0 shadow-[inset_0_0_0_1px_rgb(6_17_33/0.25)]"
				})]
			}),
			/* @__PURE__ */ jsx("figcaption", {
				className: "flex min-h-16 items-center justify-center px-1 py-3 text-center font-script text-[1.35rem] leading-tight",
				children: caption
			})
		]
	});
	if (!href) return body;
	return /* @__PURE__ */ jsx(Link, {
		href,
		className: "block rounded-sm focus-visible:outline-offset-8",
		children: body
	});
}
/** A photo-less "sky": night-blue gradient, three points of light and the report type. */
function NightSkyArt({ label, seed = 1 }) {
	const rand = (n) => (Math.sin(seed * 91.7 + n * 13.3) * .5 + .5) * 100;
	const lights = [
		0,
		1,
		2
	].map((i) => ({
		x: 15 + rand(i) * .7,
		y: 12 + rand(i + 5) * .55,
		big: i === 0
	}));
	return /* @__PURE__ */ jsxs("div", {
		className: "absolute inset-0 bg-[radial-gradient(90%_70%_at_30%_10%,var(--color-night-blue),var(--color-night))]",
		children: [
			lights.map((l, i) => /* @__PURE__ */ jsx("span", {
				className: `absolute rounded-full bg-beam-glow ${l.big ? "size-2.5 shadow-[0_0_18px_4px_rgb(84_201_51/0.55)]" : "size-1.5 shadow-[0_0_10px_2px_rgb(173_219_161/0.45)]"}`,
				style: {
					left: `${l.x}%`,
					top: `${l.y}%`
				}
			}, i)),
			/* @__PURE__ */ jsx("span", { className: "absolute inset-x-0 bottom-0 h-1/4 bg-[linear-gradient(to_top,rgb(73_67_131/0.5),transparent)]" }),
			label && /* @__PURE__ */ jsx("span", {
				className: "absolute bottom-3 left-3 font-script text-2xl leading-none text-moonlight/90",
				children: label
			})
		]
	});
}
//#endregion
//#region resources/js/Components/Ui/TicketCard.tsx
/**
* Admission ticket: art on top, a dashed tear line with punched holes on both
* edges, then the details. Corner label like a stamped stub.
*/
function TicketCard({ href, art, label, title, meta, price, comparePrice, className = "" }) {
	const card = /* @__PURE__ */ jsxs("article", {
		className: `group/ticket ticket-punch relative flex h-full flex-col bg-night-blue text-moonlight transition-transform duration-300 ease-snap [--punch-y:66%] [@media(hover:hover)]:hover:-translate-y-1.5 ${className}`,
		style: { borderRadius: "18px" },
		children: [
			/* @__PURE__ */ jsxs("div", {
				className: "relative aspect-[4/3] overflow-hidden rounded-t-[18px]",
				children: [art, label && /* @__PURE__ */ jsx("span", {
					className: "absolute top-4 -right-1 rotate-3 rounded-l-full bg-car px-3 py-1 font-display text-[0.62rem] font-bold tracking-[0.12em] text-night uppercase shadow-sm",
					children: label
				})]
			}),
			/* @__PURE__ */ jsx("div", {
				"aria-hidden": true,
				className: "mx-5 border-t-2 border-dashed border-moonlight/25"
			}),
			/* @__PURE__ */ jsxs("div", {
				className: "flex flex-1 flex-col gap-2 px-6 pt-5 pb-6",
				children: [
					/* @__PURE__ */ jsx("h3", {
						className: "font-display text-lg leading-tight font-bold tracking-[0.03em] uppercase",
						children: title
					}),
					meta && /* @__PURE__ */ jsx("div", {
						className: "text-sm text-moonlight/70",
						children: meta
					}),
					/* @__PURE__ */ jsxs("p", {
						className: "mt-auto flex items-baseline gap-2 pt-2",
						children: [/* @__PURE__ */ jsx("span", {
							className: "font-display text-2xl font-extrabold text-car",
							children: price
						}), comparePrice && /* @__PURE__ */ jsx("s", {
							className: "text-sm text-moonlight/50",
							children: comparePrice
						})]
					})
				]
			})
		]
	});
	return href ? /* @__PURE__ */ jsx(Link, {
		href,
		className: "block h-full rounded-[18px]",
		children: card
	}) : card;
}
//#endregion
export { InfoCard as a, Marquee as i, NightSkyArt as n, Polaroid as r, TicketCard as t };
