import { a as ease, c as motion, d as Seal, f as SealArt, i as duration, l as react_exports, n as useToast, o as easeFn, r as STAGGER, s as spring, u as Section } from "./Toast-DbyDChDd.js";
import { a as ARAUCARIAS, c as SaucerShape, d as shortDate, f as t, i as Starfield, l as YellowCarShape, n as Display, o as AraucariaShape, p as Button, r as Eyebrow, s as SERRA, t as Badge, u as money } from "./Typography-BT6DGpEB.js";
import { t as WaitlistForm } from "./WaitlistForm-BgqhhoUs.js";
import { n as SeoHead, t as PublicLayout } from "./PublicLayout-BFbm0IB7.js";
import { a as InfoCard, i as Marquee, n as NightSkyArt, r as Polaroid, t as TicketCard } from "./TicketCard-Vqwu0LJq.js";
import { Link, usePage } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import { useEffect, useId, useRef, useSyncExternalStore } from "react";
//#region resources/js/hooks/usePrefersReducedMotion.ts
var QUERY = "(prefers-reduced-motion: reduce)";
function subscribe(onChange) {
	const media = window.matchMedia(QUERY);
	media.addEventListener("change", onChange);
	return () => media.removeEventListener("change", onChange);
}
/**
* SSR-safe reduced-motion flag: the server (and the hydration pass) always
* render the full-motion markup, then the client switches if the user asked
* for less motion. Avoids hydration mismatches.
*/
function usePrefersReducedMotion() {
	return useSyncExternalStore(subscribe, () => window.matchMedia(QUERY).matches, () => false);
}
//#endregion
//#region resources/js/Components/Home/AbductionHero.tsx
var CAR = {
	x: 800,
	y: 873
};
var UFO_HOVER_Y = 430;
var CAR_LIFT = -(CAR.y - UFO_HOVER_Y - 30);
var PARTICLES = [
	-70,
	-42,
	-18,
	6,
	30,
	52,
	76,
	-56,
	18,
	64
];
/**
* Section 01. One orchestrated moment, driven by scroll rather than time:
* the seal lifts away, the saucer comes down the faint beam, the green light
* opens and the yellow car of the legend goes up. Scrolling back reverses it.
* Reduced motion: a still night scene, no pinning.
*/
function AbductionHero() {
	const ref = useRef(null);
	const reduced = usePrefersReducedMotion();
	const { scrollYProgress } = (0, react_exports.useScroll)({
		target: ref,
		offset: ["start start", "end end"]
	});
	const still = (0, react_exports.useMotionValue)(0);
	const p = reduced ? still : scrollYProgress;
	return /* @__PURE__ */ jsx("section", {
		ref,
		"data-tone": "dark",
		"aria-label": t.hero.sceneLabel,
		className: `relative bg-night text-moonlight ${reduced ? "h-svh min-h-[640px]" : "h-[210svh] min-h-[1300px]"}`,
		children: /* @__PURE__ */ jsxs("div", {
			className: "sticky top-0 h-svh min-h-[640px] overflow-hidden",
			children: [
				/* @__PURE__ */ jsx(Sky, { p }),
				/* @__PURE__ */ jsx(Scene, { p }),
				/* @__PURE__ */ jsx(Overlay, { p })
			]
		})
	});
}
function Sky({ p }) {
	const starsY = (0, react_exports.useTransform)(p, [0, 1], ["translateY(0px)", "translateY(-36px)"]);
	return /* @__PURE__ */ jsxs(Fragment, { children: [
		/* @__PURE__ */ jsx("div", {
			"aria-hidden": true,
			className: "absolute inset-0 bg-[radial-gradient(120%_80%_at_50%_0%,var(--color-night-blue)_0%,var(--color-night)_62%)]"
		}),
		/* @__PURE__ */ jsx(motion.div, {
			"aria-hidden": true,
			className: "absolute inset-0",
			style: { transform: starsY },
			children: /* @__PURE__ */ jsx(Starfield, { density: "medium" })
		}),
		/* @__PURE__ */ jsx("div", {
			"aria-hidden": true,
			className: "absolute inset-x-0 bottom-0 h-[55%] bg-[linear-gradient(to_bottom,transparent,rgb(73_67_131/0.42)_70%,rgb(73_67_131/0.2))]"
		})
	] });
}
function Scene({ p }) {
	const uid = useId().replace(/:/g, "");
	const beamGradient = `beam-${uid}`;
	const coreGradient = `beam-core-${uid}`;
	const halo = `halo-${uid}`;
	const ufoY = (0, react_exports.useTransform)(p, [
		.1,
		.36,
		.86,
		1
	], [
		-220,
		UFO_HOVER_Y,
		UFO_HOVER_Y,
		-260
	], { ease: [
		easeFn.snap,
		easeFn.linear,
		easeFn.glide
	] });
	const ufoX = (0, react_exports.useTransform)(p, [.86, 1], [0, 880], { ease: easeFn.glide });
	const ufoScale = (0, react_exports.useTransform)(p, [.86, 1], [1, .42]);
	const beamScale = (0, react_exports.useTransform)(p, [
		.34,
		.44,
		.8,
		.88
	], [
		.04,
		1,
		1,
		0
	]);
	const beamOpacity = (0, react_exports.useTransform)(p, [
		.32,
		.4,
		.82,
		.88
	], [
		0,
		1,
		1,
		0
	]);
	const carY = (0, react_exports.useTransform)(p, [.46, .8], [0, CAR_LIFT], { ease: easeFn.glide });
	const carScale = (0, react_exports.useTransform)(p, [.46, .8], [1, .3]);
	const carRotate = (0, react_exports.useTransform)(p, [
		.38,
		.41,
		.44,
		.47,
		.52,
		.8
	], [
		0,
		-4,
		3,
		-2,
		0,
		-16
	]);
	const carOpacity = (0, react_exports.useTransform)(p, [.77, .83], [1, 0]);
	const shadowScale = (0, react_exports.useTransform)(p, [.44, .72], [1, .15]);
	const shadowOpacity = (0, react_exports.useTransform)(p, [.44, .72], [.45, 0]);
	const groundGlow = (0, react_exports.useTransform)(p, [
		.36,
		.44,
		.8,
		.88
	], [
		0,
		.22,
		.22,
		0
	]);
	const flashOpacity = (0, react_exports.useTransform)(p, [
		.79,
		.83,
		.9
	], [
		0,
		1,
		0
	]);
	const flashScale = (0, react_exports.useTransform)(p, [.79, .9], [.6, 1.6]);
	return /* @__PURE__ */ jsxs("svg", {
		"aria-hidden": true,
		viewBox: "0 0 1600 1000",
		preserveAspectRatio: "xMidYMax slice",
		className: "absolute inset-0 h-full w-full",
		children: [
			/* @__PURE__ */ jsxs("defs", { children: [
				/* @__PURE__ */ jsxs("linearGradient", {
					id: beamGradient,
					x1: "0",
					y1: "0",
					x2: "0",
					y2: "1",
					children: [
						/* @__PURE__ */ jsx("stop", {
							offset: "0%",
							stopColor: "var(--color-beam)",
							stopOpacity: "0.75"
						}),
						/* @__PURE__ */ jsx("stop", {
							offset: "70%",
							stopColor: "var(--color-beam)",
							stopOpacity: "0.28"
						}),
						/* @__PURE__ */ jsx("stop", {
							offset: "100%",
							stopColor: "var(--color-beam-glow)",
							stopOpacity: "0.12"
						})
					]
				}),
				/* @__PURE__ */ jsxs("linearGradient", {
					id: coreGradient,
					x1: "0",
					y1: "0",
					x2: "0",
					y2: "1",
					children: [/* @__PURE__ */ jsx("stop", {
						offset: "0%",
						stopColor: "var(--color-beam-glow)",
						stopOpacity: "0.9"
					}), /* @__PURE__ */ jsx("stop", {
						offset: "100%",
						stopColor: "var(--color-beam-glow)",
						stopOpacity: "0"
					})]
				}),
				/* @__PURE__ */ jsxs("radialGradient", {
					id: halo,
					children: [/* @__PURE__ */ jsx("stop", {
						offset: "0%",
						stopColor: "var(--color-beam-glow)",
						stopOpacity: "0.9"
					}), /* @__PURE__ */ jsx("stop", {
						offset: "100%",
						stopColor: "var(--color-beam)",
						stopOpacity: "0"
					})]
				})
			] }),
			/* @__PURE__ */ jsx("path", {
				d: SERRA.far,
				fill: "var(--color-night-blue)"
			}),
			ARAUCARIAS.far.map((tree) => /* @__PURE__ */ jsx("g", {
				transform: `translate(${tree.x} ${tree.y})`,
				opacity: .9,
				children: /* @__PURE__ */ jsx(AraucariaShape, {
					height: tree.h,
					seed: tree.seed,
					fill: "rgb(6 17 33 / 0.55)"
				})
			}, tree.x)),
			/* @__PURE__ */ jsx("g", {
				transform: `translate(${CAR.x} 444)`,
				children: /* @__PURE__ */ jsxs(motion.g, {
					style: {
						scaleX: beamScale,
						opacity: beamOpacity,
						originY: 0
					},
					children: [
						/* @__PURE__ */ jsx("path", {
							d: "M-44 0 L44 0 L170 470 L-170 470 Z",
							fill: `url(#${beamGradient})`
						}),
						/* @__PURE__ */ jsx("path", {
							d: "M-16 0 L16 0 L60 470 L-60 470 Z",
							fill: `url(#${coreGradient})`
						}),
						/* @__PURE__ */ jsx("g", {
							transform: "translate(0 440)",
							children: PARTICLES.map((x, i) => /* @__PURE__ */ jsx("circle", {
								cx: x,
								cy: 0,
								r: i % 3 === 0 ? 2.6 : 1.6,
								fill: "var(--color-beam-glow)",
								className: "animate-rise",
								style: { animationDelay: `${i * 280}ms` }
							}, i))
						})
					]
				})
			}),
			/* @__PURE__ */ jsx("path", {
				d: SERRA.near,
				fill: "var(--color-night)"
			}),
			ARAUCARIAS.near.map((tree) => /* @__PURE__ */ jsx("g", {
				transform: `translate(${tree.x} ${tree.y})`,
				children: /* @__PURE__ */ jsx(AraucariaShape, {
					height: tree.h,
					seed: tree.seed
				})
			}, tree.x)),
			/* @__PURE__ */ jsxs("g", {
				transform: `translate(${CAR.x} ${CAR.y})`,
				children: [
					/* @__PURE__ */ jsx(motion.ellipse, {
						cx: 0,
						cy: 2,
						rx: 150,
						ry: 14,
						fill: "var(--color-beam)",
						style: { opacity: groundGlow }
					}),
					/* @__PURE__ */ jsx(motion.ellipse, {
						cx: 0,
						cy: 1,
						rx: 66,
						ry: 6,
						fill: "#000",
						style: {
							scaleX: shadowScale,
							opacity: shadowOpacity
						}
					}),
					/* @__PURE__ */ jsx(motion.g, {
						style: {
							y: carY,
							scale: carScale,
							rotate: carRotate,
							opacity: carOpacity
						},
						children: /* @__PURE__ */ jsx(YellowCarShape, {})
					})
				]
			}),
			/* @__PURE__ */ jsx("g", {
				transform: `translate(${CAR.x} 0)`,
				children: /* @__PURE__ */ jsxs(motion.g, {
					style: {
						x: ufoX,
						y: ufoY,
						scale: ufoScale
					},
					children: [/* @__PURE__ */ jsx(motion.circle, {
						r: 120,
						fill: `url(#${halo})`,
						style: {
							opacity: flashOpacity,
							scale: flashScale
						}
					}), /* @__PURE__ */ jsx("g", {
						className: "animate-hover-bob",
						children: /* @__PURE__ */ jsx(SaucerShape, { lightsClassName: "animate-blink" })
					})]
				})
			})
		]
	});
}
function Overlay({ p }) {
	const sealTransform = (0, react_exports.useTransform)(p, [0, .3], ["translateY(0px) scale(1)", "translateY(-140px) scale(0.76)"]);
	const sealOpacity = (0, react_exports.useTransform)(p, [.08, .28], [1, 0]);
	const cueOpacity = (0, react_exports.useTransform)(p, [0, .05], [1, 0]);
	const introBeamOpacity = (0, react_exports.useTransform)(p, [0, .16], [1, 0]);
	const punchOpacity = (0, react_exports.useTransform)(p, [.9, .97], [0, 1]);
	const punchTransform = (0, react_exports.useTransform)(p, [.9, .97], ["translateY(14px)", "translateY(0px)"]);
	return /* @__PURE__ */ jsxs(Fragment, { children: [
		/* @__PURE__ */ jsx(motion.div, {
			"aria-hidden": true,
			style: { opacity: introBeamOpacity },
			className: "absolute top-0 left-1/2 h-[46%] w-[clamp(5rem,12vw,10rem)] -translate-x-1/2 animate-beam-pulse bg-[linear-gradient(to_bottom,rgb(84_201_51/0),rgb(84_201_51/0.12)_55%,rgb(84_201_51/0))] blur-md motion-reduce:hidden"
		}),
		/* @__PURE__ */ jsxs(motion.div, {
			style: {
				transform: sealTransform,
				opacity: sealOpacity
			},
			className: "absolute inset-x-0 top-[clamp(5.5rem,14svh,9rem)] flex flex-col items-center gap-6",
			children: [/* @__PURE__ */ jsx("div", {
				className: "animate-seal-in",
				children: /* @__PURE__ */ jsx(Seal, {
					size: "lg",
					glow: true
				})
			}), /* @__PURE__ */ jsx("p", {
				className: "animate-fade-up rounded-full border border-moonlight/30 bg-moonlight/15 px-5 py-1.5 font-script text-2xl leading-none text-moonlight [animation-delay:450ms]",
				children: t.hero.freeVigil
			})]
		}),
		/* @__PURE__ */ jsx(motion.p, {
			style: {
				opacity: punchOpacity,
				transform: punchTransform
			},
			className: "absolute inset-x-0 bottom-[30%] px-6 text-center font-script text-[clamp(1.8rem,1.2rem+2.6vw,3.2rem)] leading-tight text-beam-glow",
			children: t.hero.punchline
		}),
		/* @__PURE__ */ jsxs(motion.div, {
			style: { opacity: cueOpacity },
			className: "absolute inset-x-0 bottom-[max(1.25rem,env(safe-area-inset-bottom))] flex animate-fade-up flex-col items-center gap-1 text-moonlight/85 [animation-delay:800ms]",
			children: [/* @__PURE__ */ jsx("span", {
				className: "font-script text-xl",
				children: t.hero.scroll
			}), /* @__PURE__ */ jsx("svg", {
				"aria-hidden": true,
				viewBox: "0 0 24 24",
				className: "size-5 animate-nudge motion-reduce:animate-none",
				fill: "none",
				children: /* @__PURE__ */ jsx("path", {
					d: "M6 9l6 6 6-6",
					stroke: "currentColor",
					strokeWidth: "2",
					strokeLinecap: "round",
					strokeLinejoin: "round"
				})
			})]
		})
	] });
}
//#endregion
//#region resources/js/Components/Ui/Reveal.tsx
var container = {
	hidden: {},
	shown: { transition: { staggerChildren: STAGGER } }
};
var revealItem = {
	hidden: {
		opacity: 0,
		transform: "translateY(16px)"
	},
	shown: {
		opacity: 1,
		transform: "translateY(0px)",
		transition: {
			duration: duration.reveal,
			ease: ease.snap
		}
	}
};
/**
* Fades a block in once, when it enters the viewport. With `stagger`, direct
* <RevealItem> children enter 80ms apart. Reduced motion renders content as-is.
*/
function Reveal({ children, stagger = false, className = "", as = "div" }) {
	const reduced = usePrefersReducedMotion();
	const Tag = as === "ul" ? motion.ul : as === "ol" ? motion.ol : motion.div;
	if (reduced) return /* @__PURE__ */ jsx(as, {
		className,
		children
	});
	return /* @__PURE__ */ jsx(Tag, {
		className,
		initial: "hidden",
		whileInView: "shown",
		viewport: {
			once: true,
			margin: "0px 0px -12% 0px"
		},
		variants: stagger ? container : revealItem,
		children
	});
}
function RevealItem({ children, className = "", as = "div" }) {
	if (usePrefersReducedMotion()) return /* @__PURE__ */ jsx(as, {
		className,
		children
	});
	const Tag = as === "li" ? motion.li : motion.div;
	return /* @__PURE__ */ jsx(Tag, {
		className,
		variants: revealItem,
		children
	});
}
//#endregion
//#region resources/js/Components/Home/Postcard.tsx
function Postmark() {
	const id = `postmark-${useId().replace(/:/g, "")}`;
	return /* @__PURE__ */ jsxs("svg", {
		"aria-hidden": true,
		viewBox: "0 0 120 120",
		className: "absolute -top-4 right-16 w-28 -rotate-12 text-horizon opacity-80 mix-blend-multiply sm:right-24",
		children: [
			/* @__PURE__ */ jsx("defs", { children: /* @__PURE__ */ jsx("path", {
				id,
				d: "M 60 60 m -40 0 a 40 40 0 1 1 80 0 a 40 40 0 1 1 -80 0"
			}) }),
			/* @__PURE__ */ jsx("circle", {
				cx: "60",
				cy: "60",
				r: "52",
				fill: "none",
				stroke: "currentColor",
				strokeWidth: "2.5"
			}),
			/* @__PURE__ */ jsx("circle", {
				cx: "60",
				cy: "60",
				r: "28",
				fill: "none",
				stroke: "currentColor",
				strokeWidth: "1.5"
			}),
			/* @__PURE__ */ jsx("text", {
				fill: "currentColor",
				fontFamily: "var(--font-display)",
				fontWeight: "800",
				fontSize: "11",
				letterSpacing: "3",
				children: /* @__PURE__ */ jsx("textPath", {
					href: `#${id}`,
					children: "LAGES · SC · LAGES · SC ·"
				})
			}),
			/* @__PURE__ */ jsx("text", {
				x: "60",
				y: "64",
				textAnchor: "middle",
				fill: "currentColor",
				fontFamily: "var(--font-script)",
				fontSize: "16",
				fontWeight: "600",
				children: "2028"
			}),
			[
				0,
				1,
				2,
				3
			].map((i) => /* @__PURE__ */ jsx("path", {
				d: `M118 ${44 + i * 10} C 140 ${40 + i * 10} 160 ${48 + i * 10} 190 ${44 + i * 10}`,
				stroke: "currentColor",
				strokeWidth: "2",
				fill: "none"
			}, i))
		]
	});
}
/** "Mande um postal": a postcard from the serra, shareable on WhatsApp or by link. */
function Postcard() {
	const { appUrl } = usePage().props;
	const toast = useToast();
	const shareUrl = `https://wa.me/?text=${encodeURIComponent(t.community.shareText(appUrl))}`;
	const copy = async () => {
		try {
			await navigator.clipboard.writeText(appUrl);
			toast(t.community.copied);
		} catch {
			toast(appUrl);
		}
	};
	return /* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsx("div", {
		className: "relative rotate-[1.5deg] rounded-[6px] bg-moonlight p-5 text-night shadow-polaroid sm:p-7",
		children: /* @__PURE__ */ jsxs("div", {
			className: "grid gap-6 sm:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]",
			children: [/* @__PURE__ */ jsxs("div", {
				className: "flex items-center gap-4 sm:flex-col sm:items-start",
				children: [/* @__PURE__ */ jsx("div", {
					className: "w-24 shrink-0 -rotate-6 sm:w-32",
					children: /* @__PURE__ */ jsx(SealArt, { title: "Selo OVNIPORTO no postal" })
				}), /* @__PURE__ */ jsx("p", {
					className: "font-script text-[1.45rem] leading-snug text-night/85",
					children: t.community.postcardMessage
				})]
			}), /* @__PURE__ */ jsxs("div", {
				className: "relative border-night/15 sm:border-l-2 sm:border-dashed sm:pl-6",
				children: [
					/* @__PURE__ */ jsx("div", {
						className: "stamp-edge ml-auto flex size-24 items-center justify-center bg-car",
						children: /* @__PURE__ */ jsx("div", {
							className: "flex h-full w-full items-center justify-center bg-night-blue",
							children: /* @__PURE__ */ jsx("svg", {
								viewBox: "-120 -60 240 100",
								className: "w-16",
								"aria-hidden": true,
								children: /* @__PURE__ */ jsx(SaucerShape, {})
							})
						})
					}),
					/* @__PURE__ */ jsx(Postmark, {}),
					/* @__PURE__ */ jsxs("div", {
						className: "mt-6 space-y-4 font-script text-xl text-night/70",
						children: [
							/* @__PURE__ */ jsx("p", {
								className: "border-b border-night/25 pb-1",
								children: t.community.postcardTo
							}),
							/* @__PURE__ */ jsx("p", {
								className: "border-b border-night/25 pb-1",
								children: "Serra Catarinense"
							}),
							/* @__PURE__ */ jsx("p", {
								className: "border-b border-night/25 pb-1",
								children: t.brand.city
							})
						]
					})
				]
			})]
		})
	}), /* @__PURE__ */ jsxs("div", {
		className: "mt-8 flex flex-wrap gap-3",
		children: [/* @__PURE__ */ jsx(Button, {
			href: shareUrl,
			external: true,
			tone: "dark",
			children: t.community.shareWhatsapp
		}), /* @__PURE__ */ jsx(Button, {
			variant: "secondary",
			tone: "dark",
			onClick: copy,
			children: t.community.copy
		})]
	})] });
}
//#endregion
//#region resources/js/Components/Home/CommunitySection.tsx
function SocialButton({ href, label }) {
	if (href) return /* @__PURE__ */ jsx(Button, {
		href,
		variant: "secondary",
		tone: "dark",
		external: true,
		children: label
	});
	return /* @__PURE__ */ jsxs("span", {
		className: "inline-flex h-12 items-center gap-2 rounded-full border-[1.5px] border-dashed border-moonlight/30 px-5 text-[0.95rem] font-semibold text-moonlight/60",
		children: [label, /* @__PURE__ */ jsx(Badge, {
			tone: "neutral",
			children: t.community.linkSoon
		})]
	});
}
/** Section 09. Join the vigil, ask to be told when the campaign opens, send a postcard. */
function CommunitySection({ whatsapp, instagram }) {
	return /* @__PURE__ */ jsxs(Section, {
		tone: "dark",
		pattern: "stars",
		wave: true,
		labelledBy: "comunidade",
		className: "pb-10",
		children: [/* @__PURE__ */ jsxs("div", {
			className: "flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between",
			children: [/* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsx(Eyebrow, {
				tone: "dark",
				children: t.community.eyebrow
			}), /* @__PURE__ */ jsx(Display, {
				as: "h2",
				id: "comunidade",
				className: "mt-3",
				children: t.community.title
			})] }), /* @__PURE__ */ jsxs("div", {
				className: "flex flex-wrap gap-3",
				children: [
					/* @__PURE__ */ jsx(Button, {
						href: "/comunidade",
						children: t.community.google
					}),
					/* @__PURE__ */ jsx(SocialButton, {
						href: whatsapp,
						label: t.community.whatsapp
					}),
					/* @__PURE__ */ jsx(SocialButton, {
						href: instagram,
						label: t.community.instagram
					})
				]
			})]
		}), /* @__PURE__ */ jsxs("div", {
			className: "mt-14 grid gap-16 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)] lg:gap-20",
			children: [/* @__PURE__ */ jsx(Reveal, { children: /* @__PURE__ */ jsxs("div", {
				className: "rounded-[26px] bg-night-blue/70 p-6 ring-1 ring-moonlight/10 sm:p-8",
				children: [
					/* @__PURE__ */ jsx("h3", {
						className: "font-display text-xl font-bold tracking-[0.03em] uppercase",
						children: t.community.waitlistTitle
					}),
					/* @__PURE__ */ jsx("p", {
						className: "mt-2 mb-6 max-w-[46ch] text-moonlight/75",
						children: t.community.waitlistLead
					}),
					/* @__PURE__ */ jsx(WaitlistForm, {})
				]
			}) }), /* @__PURE__ */ jsxs(Reveal, { children: [
				/* @__PURE__ */ jsx("h3", {
					className: "font-display text-xl font-bold tracking-[0.03em] uppercase",
					children: t.community.postcardTitle
				}),
				/* @__PURE__ */ jsx("p", {
					className: "mt-2 mb-10 text-moonlight/75",
					children: t.community.postcardLead
				}),
				/* @__PURE__ */ jsx(Postcard, {})
			] })]
		})]
	});
}
//#endregion
//#region resources/js/Components/Home/LegendSection.tsx
function CarPortrait() {
	return /* @__PURE__ */ jsxs("svg", {
		viewBox: "0 0 400 300",
		role: "img",
		"aria-label": "Ilustração provisória do carro amarelo da lenda",
		className: "h-full w-full",
		children: [
			/* @__PURE__ */ jsx("rect", {
				width: "400",
				height: "300",
				fill: "var(--color-night-blue)"
			}),
			/* @__PURE__ */ jsx("path", {
				d: "M150 0 L250 0 L320 300 L80 300 Z",
				fill: "var(--color-beam)",
				opacity: "0.16"
			}),
			/* @__PURE__ */ jsx("path", {
				d: "M185 0 L215 0 L250 300 L150 300 Z",
				fill: "var(--color-beam-glow)",
				opacity: "0.14"
			}),
			/* @__PURE__ */ jsx("g", {
				transform: "translate(200 190) rotate(-12) scale(1.45)",
				children: /* @__PURE__ */ jsx(YellowCarShape, { headlights: true })
			}),
			/* @__PURE__ */ jsx("path", {
				d: "M0 262 C120 250 280 250 400 262 L400 300 L0 300 Z",
				fill: "var(--color-night)"
			})
		]
	});
}
/** Section 07. Asymmetric: the car on a big polaroid, the legend (not yet written) beside it. */
function LegendSection({ teaser, pending }) {
	return /* @__PURE__ */ jsx(Section, {
		tone: "light",
		labelledBy: "lenda",
		wave: true,
		children: /* @__PURE__ */ jsxs("div", {
			className: "grid items-center gap-14 md:grid-cols-[minmax(0,1fr)_minmax(0,1.05fr)] lg:gap-24",
			children: [/* @__PURE__ */ jsx(Reveal, {
				className: "mx-auto w-full max-w-md md:mx-0",
				children: /* @__PURE__ */ jsx(Polaroid, {
					rotate: -3,
					tape: "corner",
					art: /* @__PURE__ */ jsx(CarPortrait, {}),
					imageClassName: "aspect-[4/3]",
					caption: pending ? t.legend.polaroid : "O carro amarelo",
					href: "/lenda"
				})
			}), /* @__PURE__ */ jsxs(Reveal, { children: [
				/* @__PURE__ */ jsx(Eyebrow, { children: t.legend.eyebrow }),
				/* @__PURE__ */ jsx(Display, {
					as: "h3",
					id: "lenda",
					className: "mt-3 max-w-[14ch]",
					children: t.legend.title
				}),
				pending && /* @__PURE__ */ jsx(Badge, {
					tone: "neutral",
					className: "mt-6",
					children: t.legend.pending
				}),
				/* @__PURE__ */ jsx("p", {
					className: "mt-6 max-w-[48ch] text-lg leading-relaxed text-night/80",
					children: teaser
				}),
				/* @__PURE__ */ jsx("div", {
					className: "mt-8",
					children: /* @__PURE__ */ jsx(Button, {
						href: "/lenda",
						variant: "ghost",
						iconRight: /* @__PURE__ */ jsx("span", {
							"aria-hidden": true,
							children: "→"
						}),
						children: t.legend.cta
					})
				})
			] })]
		})
	});
}
//#endregion
//#region resources/js/Components/Home/LogbookSection.tsx
var ROTATIONS = [
	-4,
	3,
	-2,
	4
];
var OFFSETS = [
	"lg:translate-y-0",
	"lg:translate-y-14",
	"lg:translate-y-4",
	"lg:translate-y-20"
];
var table = {
	hidden: {},
	shown: { transition: { staggerChildren: STAGGER } }
};
var drop = (rotate) => ({
	hidden: {
		opacity: 0,
		transform: `translateY(-36px) rotate(${rotate + 7}deg)`
	},
	shown: {
		opacity: 1,
		transform: `translateY(0px) rotate(0deg)`,
		transition: spring.paper
	}
});
/** Section 03. The latest approved reports, thrown on the table like instant photos. */
function LogbookSection({ sightings }) {
	const reduced = usePrefersReducedMotion();
	const cards = sightings.length > 0 ? sightings.slice(0, 4) : null;
	return /* @__PURE__ */ jsxs(Section, {
		tone: "dark",
		pattern: "stars",
		wave: true,
		labelledBy: "livro",
		innerClassName: "pb-[clamp(6rem,4rem+6vw,10rem)]!",
		children: [/* @__PURE__ */ jsxs("div", {
			className: "flex flex-col gap-8 lg:flex-row lg:items-end lg:justify-between",
			children: [/* @__PURE__ */ jsxs("div", {
				className: "min-w-0 lg:flex-1",
				children: [
					/* @__PURE__ */ jsx(Eyebrow, {
						tone: "dark",
						children: t.logbook.eyebrow
					}),
					/* @__PURE__ */ jsx(Display, {
						as: "h2",
						id: "livro",
						className: "mt-3",
						children: t.logbook.title
					}),
					/* @__PURE__ */ jsx("p", {
						className: "mt-5 max-w-[48ch] text-lg text-moonlight/75",
						children: t.logbook.lead
					})
				]
			}), /* @__PURE__ */ jsxs("div", {
				className: "flex flex-wrap gap-3 lg:justify-end",
				children: [/* @__PURE__ */ jsx(Button, {
					href: "/relatar",
					children: t.logbook.report
				}), /* @__PURE__ */ jsx(Button, {
					href: "/mapa",
					variant: "secondary",
					tone: "dark",
					children: t.logbook.map
				})]
			})]
		}), /* @__PURE__ */ jsx(motion.ul, {
			initial: reduced ? false : "hidden",
			whileInView: "shown",
			viewport: {
				once: true,
				margin: "0px 0px -15% 0px"
			},
			variants: table,
			className: "mt-16 grid grid-cols-2 gap-x-5 gap-y-10 sm:gap-x-8 lg:grid-cols-4 lg:gap-x-10",
			children: ROTATIONS.map((rotate, i) => {
				const sighting = cards?.[i];
				return /* @__PURE__ */ jsx(motion.li, {
					variants: drop(rotate),
					className: OFFSETS[i],
					children: sighting ? /* @__PURE__ */ jsx(Polaroid, {
						href: `/mapa#relato-${sighting.id}`,
						rotate,
						tape: i % 2 === 0 ? "top" : "corner",
						src: sighting.photo,
						alt: `${t.logbook.types[sighting.type]} vista por ${sighting.nickname}`,
						art: /* @__PURE__ */ jsx(NightSkyArt, {
							label: t.logbook.types[sighting.type],
							seed: sighting.id
						}),
						caption: `${t.logbook.types[sighting.type]} · ${sighting.place ?? t.logbook.noPlace} · ${shortDate(sighting.date)}`
					}) : /* @__PURE__ */ jsx(Polaroid, {
						href: "/relatar",
						rotate,
						tape: i % 2 === 0 ? "top" : "corner",
						art: /* @__PURE__ */ jsx(NightSkyArt, { seed: i + 3 }),
						caption: t.logbook.yourReport
					})
				}, sighting?.id ?? `empty-${i}`);
			})
		})]
	});
}
//#endregion
//#region resources/js/Components/Home/ConceptArt.tsx
/**
* Placeholder "concept" of the finished runway, composed from the scene
* primitives: stone stars on the ground, the vigil benches' red lamps, the
* suspended yellow car. Always shown with the "conceito" badge.
*/
function RunwayConceptArt() {
	return /* @__PURE__ */ jsxs("svg", {
		viewBox: "0 0 800 450",
		role: "img",
		"aria-label": "Ilustração conceitual da pista de pouso de pedra à noite",
		className: "h-full w-full",
		children: [
			/* @__PURE__ */ jsxs("defs", { children: [/* @__PURE__ */ jsxs("radialGradient", {
				id: "concept-sky",
				cx: "50%",
				cy: "0%",
				r: "100%",
				children: [/* @__PURE__ */ jsx("stop", {
					offset: "0%",
					stopColor: "var(--color-night-blue)"
				}), /* @__PURE__ */ jsx("stop", {
					offset: "100%",
					stopColor: "var(--color-night)"
				})]
			}), /* @__PURE__ */ jsxs("linearGradient", {
				id: "concept-haze",
				x1: "0",
				y1: "0",
				x2: "0",
				y2: "1",
				children: [/* @__PURE__ */ jsx("stop", {
					offset: "0%",
					stopColor: "var(--color-horizon)",
					stopOpacity: "0"
				}), /* @__PURE__ */ jsx("stop", {
					offset: "100%",
					stopColor: "var(--color-horizon)",
					stopOpacity: "0.55"
				})]
			})] }),
			/* @__PURE__ */ jsx("rect", {
				width: "800",
				height: "450",
				fill: "url(#concept-sky)"
			}),
			Array.from({ length: 40 }, (_, i) => /* @__PURE__ */ jsx("circle", {
				cx: i * 197 % 800,
				cy: i * 89 % 240,
				r: i % 5 === 0 ? 1.6 : .9,
				fill: "var(--color-moonlight)",
				opacity: .35 + i % 4 * .15
			}, i)),
			/* @__PURE__ */ jsx("rect", {
				y: "150",
				width: "800",
				height: "160",
				fill: "url(#concept-haze)"
			}),
			/* @__PURE__ */ jsx("path", {
				d: "M0 300 C140 270 260 296 400 290 C540 284 660 262 800 276 L800 450 L0 450 Z",
				fill: "var(--color-night-blue)"
			}),
			/* @__PURE__ */ jsx("g", {
				transform: "translate(110 296)",
				children: /* @__PURE__ */ jsx(AraucariaShape, {
					height: 150,
					seed: 4
				})
			}),
			/* @__PURE__ */ jsx("g", {
				transform: "translate(720 284)",
				children: /* @__PURE__ */ jsx(AraucariaShape, {
					height: 120,
					seed: 9
				})
			}),
			/* @__PURE__ */ jsx("path", {
				d: "M0 330 C200 316 600 316 800 330 L800 450 L0 450 Z",
				fill: "var(--color-night)"
			}),
			[
				{
					x: 300,
					y: 330,
					r: 26
				},
				{
					x: 420,
					y: 352,
					r: 34
				},
				{
					x: 560,
					y: 340,
					r: 22
				},
				{
					x: 190,
					y: 360,
					r: 18
				},
				{
					x: 660,
					y: 362,
					r: 28
				}
			].map(({ x, y, r }) => /* @__PURE__ */ jsx("path", {
				transform: `translate(${x} ${y}) scale(1 0.35)`,
				d: `M0 ${-r} L${r * .28} ${-r * .3} L${r} 0 L${r * .28} ${r * .3} L0 ${r} L${-r * .28} ${r * .3} L${-r} 0 L${-r * .28} ${-r * .3} Z`,
				fill: "var(--color-moonlight)",
				opacity: "0.75"
			}, x)),
			[
				150,
				250,
				520,
				640
			].map((x) => /* @__PURE__ */ jsx("circle", {
				cx: x,
				cy: 386,
				r: 3,
				fill: "var(--color-car)",
				opacity: "0.9"
			}, x)),
			/* @__PURE__ */ jsx("path", {
				d: "M560 140 L586 140 L612 236 L534 236 Z",
				fill: "var(--color-beam)",
				opacity: "0.18"
			}),
			/* @__PURE__ */ jsx("g", {
				transform: "translate(573 136) scale(0.3)",
				children: /* @__PURE__ */ jsx(SaucerShape, {})
			}),
			/* @__PURE__ */ jsx("g", {
				transform: "translate(573 236) rotate(-8) scale(0.42)",
				children: /* @__PURE__ */ jsx(YellowCarShape, {})
			})
		]
	});
}
//#endregion
//#region resources/js/Components/Home/PlaceSection.tsx
/** Section 05. The runway as it honestly is: a plan, phase by phase. */
function PlaceSection({ lead, spaces }) {
	return /* @__PURE__ */ jsxs(Section, {
		tone: "light",
		labelledBy: "o-lugar",
		innerClassName: "pt-[clamp(3rem,2rem+4vw,6rem)]!",
		children: [
			/* @__PURE__ */ jsxs("div", {
				className: "grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1.1fr)] lg:items-end",
				children: [/* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsx(Eyebrow, { children: t.place.eyebrow }), /* @__PURE__ */ jsx(Display, {
					as: "h2",
					id: "o-lugar",
					className: "mt-3",
					children: t.place.title
				})] }), /* @__PURE__ */ jsx("p", {
					className: "max-w-[52ch] text-lg leading-relaxed text-night/80 lg:pb-2",
					children: lead
				})]
			}),
			/* @__PURE__ */ jsxs(Reveal, {
				stagger: true,
				className: "mt-12 grid gap-5 md:grid-cols-2",
				children: [/* @__PURE__ */ jsx(RevealItem, { children: /* @__PURE__ */ jsxs("figure", { children: [/* @__PURE__ */ jsxs("div", {
					className: "flex aspect-[16/10] flex-col items-center justify-center gap-3 rounded-[22px] border-2 border-dashed border-night/25 bg-night/[0.03] p-6 text-center",
					children: [/* @__PURE__ */ jsxs("svg", {
						"aria-hidden": true,
						viewBox: "0 0 48 48",
						className: "size-12 text-horizon",
						fill: "none",
						children: [
							/* @__PURE__ */ jsx("rect", {
								x: "6",
								y: "12",
								width: "36",
								height: "26",
								rx: "5",
								stroke: "currentColor",
								strokeWidth: "2.5"
							}),
							/* @__PURE__ */ jsx("circle", {
								cx: "24",
								cy: "25",
								r: "7",
								stroke: "currentColor",
								strokeWidth: "2.5"
							}),
							/* @__PURE__ */ jsx("path", {
								d: "M17 12l3-5h8l3 5",
								stroke: "currentColor",
								strokeWidth: "2.5",
								strokeLinejoin: "round"
							})
						]
					}), /* @__PURE__ */ jsx("p", {
						className: "font-script text-2xl text-horizon",
						children: t.place.todayEmpty
					})]
				}), /* @__PURE__ */ jsx("figcaption", {
					className: "mt-3 px-1 text-sm font-semibold",
					children: t.place.today
				})] }) }), /* @__PURE__ */ jsx(RevealItem, { children: /* @__PURE__ */ jsxs("figure", { children: [/* @__PURE__ */ jsxs("div", {
					className: "group relative aspect-[16/10] overflow-hidden rounded-[22px] bg-night",
					children: [/* @__PURE__ */ jsx("div", {
						className: "h-full w-full transition-transform duration-700 ease-snap [@media(hover:hover)]:group-hover:scale-[1.03]",
						children: /* @__PURE__ */ jsx(RunwayConceptArt, {})
					}), /* @__PURE__ */ jsx(Badge, {
						tone: "horizon",
						className: "absolute top-4 right-4",
						children: t.place.concept
					})]
				}), /* @__PURE__ */ jsx("figcaption", {
					className: "mt-3 px-1 text-sm font-semibold",
					children: t.place.future
				})] }) })]
			}),
			/* @__PURE__ */ jsx("h3", {
				className: "mt-16 text-[0.7rem] font-semibold tracking-[0.12em] text-night/60 uppercase",
				children: t.place.spacesLabel
			}),
			/* @__PURE__ */ jsx("div", {
				tabIndex: 0,
				role: "region",
				"aria-label": t.place.spacesLabel,
				className: "-mx-5 mt-4 overflow-x-auto overscroll-x-contain px-5 pb-6 [mask-image:linear-gradient(to_right,transparent,#000_1.25rem,#000_calc(100%-3rem),transparent)] [scrollbar-width:none] sm:-mx-8 sm:px-8",
				children: /* @__PURE__ */ jsx("ol", {
					className: "flex snap-x snap-mandatory gap-4",
					children: spaces.map((space, i) => /* @__PURE__ */ jsxs("li", {
						className: `flex w-[min(78vw,17.5rem)] shrink-0 snap-start flex-col rounded-[22px] p-6 ring-1 ${space.phase === 1 ? "bg-beam/10 ring-beam/50" : "bg-night/[0.03] ring-night/12"}`,
						children: [
							/* @__PURE__ */ jsx(Display, {
								as: "span",
								outlined: true,
								className: `text-5xl leading-none ${space.phase === 1 ? "text-beam" : "text-horizon"}`,
								children: String(i + 1).padStart(2, "0")
							}),
							/* @__PURE__ */ jsx("p", {
								className: "mt-5 font-display text-[1.05rem] leading-tight font-bold tracking-[0.03em] uppercase",
								children: space.name
							}),
							/* @__PURE__ */ jsx("p", {
								className: "mt-2 text-[0.95rem] leading-snug text-night/70",
								children: space.role
							}),
							/* @__PURE__ */ jsxs("p", {
								className: "mt-auto flex flex-wrap gap-2 pt-5",
								children: [/* @__PURE__ */ jsx(Badge, {
									tone: space.phase === 1 ? "beam" : "neutral",
									children: t.place.phase(space.phase)
								}), /* @__PURE__ */ jsx(Badge, {
									tone: "car",
									children: t.place.status[space.status]
								})]
							})
						]
					}, space.slug))
				})
			}),
			/* @__PURE__ */ jsx("div", {
				className: "mt-6",
				children: /* @__PURE__ */ jsx(Button, {
					href: "/o-lugar",
					variant: "secondary",
					children: t.place.more
				})
			})
		]
	});
}
//#endregion
//#region resources/js/Components/Home/RegionSection.tsx
/** Section 08. Partners who agreed to be listed; until then, an invitation instead of a hole. */
function RegionSection({ partners, contactEmail }) {
	const mailto = `mailto:${contactEmail}?subject=${encodeURIComponent("Quero aparecer no OVNIPORTO")}`;
	return /* @__PURE__ */ jsxs(Section, {
		tone: "dusk",
		labelledBy: "regiao",
		children: [/* @__PURE__ */ jsxs("div", {
			className: "flex flex-col gap-6 md:flex-row md:items-end md:justify-between",
			children: [/* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsx(Eyebrow, { children: t.region.eyebrow }), /* @__PURE__ */ jsx(Display, {
				as: "h3",
				id: "regiao",
				className: "mt-3",
				children: t.region.title
			})] }), /* @__PURE__ */ jsx(Button, {
				href: "/regiao",
				variant: "secondary",
				children: t.region.all
			})]
		}), partners.length > 0 ? /* @__PURE__ */ jsx(Reveal, {
			stagger: true,
			as: "ul",
			className: "mt-12 grid gap-5 md:grid-cols-3",
			children: partners.map((partner) => /* @__PURE__ */ jsx(RevealItem, {
				as: "li",
				children: /* @__PURE__ */ jsxs(Link, {
					href: `/regiao#${partner.slug}`,
					className: "group flex items-center gap-4 rounded-[22px] bg-moonlight p-3 ring-1 ring-night/10 transition-shadow duration-300 ease-snap hover:shadow-lift",
					children: [/* @__PURE__ */ jsx("div", {
						className: "size-24 shrink-0 overflow-hidden rounded-2xl bg-night-blue",
						children: partner.cover && /* @__PURE__ */ jsx("img", {
							src: partner.cover,
							alt: "",
							loading: "lazy",
							className: "h-full w-full object-cover"
						})
					}), /* @__PURE__ */ jsxs("div", {
						className: "min-w-0",
						children: [
							/* @__PURE__ */ jsx("p", {
								className: "truncate font-display text-base font-bold tracking-[0.03em] uppercase",
								children: partner.name
							}),
							/* @__PURE__ */ jsxs("p", {
								className: "mt-1 text-sm text-night/70",
								children: [
									t.region.types[partner.type] ?? partner.type,
									" · ",
									partner.city
								]
							}),
							partner.isExample && /* @__PURE__ */ jsx(Badge, {
								tone: "neutral",
								className: "mt-2",
								children: t.region.example
							})
						]
					})]
				})
			}, partner.slug))
		}) : /* @__PURE__ */ jsx(Reveal, {
			className: "mt-12",
			children: /* @__PURE__ */ jsxs("div", {
				className: "relative overflow-hidden rounded-[26px] bg-moonlight px-6 py-12 ring-1 ring-night/10 sm:px-12",
				children: [
					/* @__PURE__ */ jsxs("svg", {
						"aria-hidden": true,
						viewBox: "0 0 600 200",
						className: "absolute right-0 bottom-0 h-full w-auto text-night/[0.07]",
						preserveAspectRatio: "xMaxYMax meet",
						children: [/* @__PURE__ */ jsx("g", {
							transform: "translate(420 200)",
							children: /* @__PURE__ */ jsx(AraucariaShape, {
								height: 190,
								seed: 5,
								fill: "currentColor"
							})
						}), /* @__PURE__ */ jsx("g", {
							transform: "translate(540 200)",
							children: /* @__PURE__ */ jsx(AraucariaShape, {
								height: 140,
								seed: 8,
								fill: "currentColor"
							})
						})]
					}),
					/* @__PURE__ */ jsx("p", {
						className: "relative max-w-[30ch] font-script text-[clamp(1.8rem,1.4rem+1.6vw,2.6rem)] leading-tight text-horizon",
						children: t.region.empty
					}),
					/* @__PURE__ */ jsx("div", {
						className: "relative mt-8",
						children: /* @__PURE__ */ jsx(Button, {
							href: mailto,
							variant: "primary",
							children: t.region.join
						})
					})
				]
			})
		})]
	});
}
//#endregion
//#region resources/js/Components/Home/SouvenirsSection.tsx
var SLOTS = 3;
/** Section 06. Souvenirs as admission tickets; empty slots stay as dashed stubs, never fake products. */
function SouvenirsSection({ lead, products }) {
	const stubs = Math.max(0, SLOTS - products.length);
	return /* @__PURE__ */ jsxs(Section, {
		tone: "dark",
		wave: true,
		labelledBy: "lembrancas",
		children: [/* @__PURE__ */ jsxs("div", {
			className: "grid gap-6 lg:grid-cols-[minmax(0,1fr)_auto] lg:items-end",
			children: [/* @__PURE__ */ jsxs("div", { children: [
				/* @__PURE__ */ jsx(Eyebrow, {
					tone: "dark",
					children: t.store.eyebrow
				}),
				/* @__PURE__ */ jsx(Display, {
					as: "h2",
					id: "lembrancas",
					className: "mt-3",
					children: t.store.title
				}),
				/* @__PURE__ */ jsx("p", {
					className: "mt-5 font-script text-[1.9rem] leading-none text-car",
					children: lead
				})
			] }), /* @__PURE__ */ jsx(Button, {
				href: "/loja",
				variant: "car",
				size: "lg",
				children: t.store.cta
			})]
		}), /* @__PURE__ */ jsxs(Reveal, {
			stagger: true,
			as: "ul",
			className: "mt-14 grid gap-6 sm:grid-cols-2 lg:grid-cols-3",
			children: [products.map((product) => /* @__PURE__ */ jsx(RevealItem, {
				as: "li",
				children: /* @__PURE__ */ jsx(TicketCard, {
					href: `/loja#${product.slug}`,
					label: product.label,
					title: product.name,
					price: money(product.priceCents),
					comparePrice: product.comparePriceCents ? money(product.comparePriceCents) : null,
					meta: product.madeToOrder ? t.store.madeToOrder(product.productionDays) : t.store.ready,
					art: product.image ? /* @__PURE__ */ jsx("img", {
						src: product.image,
						alt: product.imageAlt ?? product.name,
						loading: "lazy",
						className: "h-full w-full object-cover"
					}) : /* @__PURE__ */ jsx("div", {
						className: "flex h-full items-center justify-center bg-[radial-gradient(circle_at_50%_40%,rgb(84_201_51/0.22),transparent_60%)]",
						children: /* @__PURE__ */ jsx("div", {
							className: "w-[52%] -rotate-6 drop-shadow-[0_14px_20px_rgb(6_17_33/0.6)] transition-transform duration-500 ease-snap [@media(hover:hover)]:group-hover/ticket:rotate-0",
							children: /* @__PURE__ */ jsx(SealArt, { title: product.imageAlt ?? product.name })
						})
					})
				})
			}, product.id)), Array.from({ length: stubs }, (_, i) => /* @__PURE__ */ jsx(RevealItem, {
				as: "li",
				className: i > 0 ? "hidden lg:block" : "",
				children: /* @__PURE__ */ jsxs("div", {
					className: "flex h-full min-h-72 flex-col items-center justify-center gap-2 rounded-[18px] border-2 border-dashed border-moonlight/20 p-8 text-center",
					children: [/* @__PURE__ */ jsx("p", {
						className: "font-script text-2xl text-beam-glow",
						children: products.length === 0 ? t.store.empty : "Próxima lembrança em produção"
					}), /* @__PURE__ */ jsx("p", {
						className: "text-sm text-moonlight/60",
						children: "Camiseta, caneca e Kit Abdução, feitos sob pedido."
					})]
				})
			}, `stub-${i}`))]
		})]
	});
}
//#endregion
//#region resources/js/Components/Ui/CountUp.tsx
/**
* Counts from 0 to `value` the first time it scrolls into view. The server
* renders the final number (crawlers and no-JS readers get the real count);
* the client only rewinds to 0 when the number starts below the fold.
*/
function CountUp({ value, className = "" }) {
	const ref = useRef(null);
	const inView = (0, react_exports.useInView)(ref, {
		once: true,
		margin: "0px 0px -10% 0px"
	});
	const armed = useRef(false);
	useEffect(() => {
		const el = ref.current;
		if (!el || window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;
		if (el.getBoundingClientRect().top > window.innerHeight) {
			el.textContent = "0";
			armed.current = true;
		}
	}, []);
	useEffect(() => {
		const el = ref.current;
		if (!inView || !armed.current || !el) return;
		const controls = (0, react_exports.animate)(0, value, {
			duration: Math.min(1.6, .6 + value / 60),
			ease: ease.snap,
			onUpdate: (v) => {
				el.textContent = Math.round(v).toLocaleString("pt-BR");
			}
		});
		return () => controls.stop();
	}, [inView, value]);
	return /* @__PURE__ */ jsx("span", {
		ref,
		className: `tabular-nums ${className}`,
		children: value.toLocaleString("pt-BR")
	});
}
//#endregion
//#region resources/js/Components/Home/WelcomeSection.tsx
/**
* Section 02. The wordmark set at poster scale, then the facts laid out like the
* fields of a boarding pass, separated by perforations.
*/
function WelcomeSection({ intro, sightingsCount }) {
	return /* @__PURE__ */ jsxs(Section, {
		tone: "light",
		wave: true,
		labelledBy: "boas-vindas",
		innerClassName: "text-center",
		children: [/* @__PURE__ */ jsxs(Reveal, { children: [
			/* @__PURE__ */ jsx(Eyebrow, { children: t.welcome.eyebrow }),
			/* @__PURE__ */ jsx(Display, {
				as: "h1",
				id: "boas-vindas",
				className: "mt-3 -mr-[0.04em]",
				children: t.brand.name
			}),
			/* @__PURE__ */ jsx("p", {
				className: "mt-4 text-[clamp(1.05rem,0.95rem+0.5vw,1.35rem)] font-semibold text-horizon",
				children: t.brand.tagline
			}),
			/* @__PURE__ */ jsx("p", {
				className: "mx-auto mt-8 max-w-[62ch] text-[1.075rem] leading-relaxed text-night/80",
				children: intro
			})
		] }), /* @__PURE__ */ jsx(Reveal, {
			className: "mx-auto mt-14 max-w-5xl",
			children: /* @__PURE__ */ jsx("dl", {
				className: "grid grid-cols-2 rounded-[22px] bg-night/[0.035] text-left ring-1 ring-night/10 md:grid-cols-4",
				children: [
					/* @__PURE__ */ jsx(InfoCard, {
						label: t.welcome.where,
						value: t.welcome.whereValue,
						href: "/o-lugar"
					}, "onde"),
					/* @__PURE__ */ jsx(InfoCard, {
						label: t.welcome.city,
						value: t.welcome.cityValue
					}, "cidade"),
					/* @__PURE__ */ jsx(InfoCard, {
						label: t.welcome.sightings,
						value: /* @__PURE__ */ jsxs("span", { children: [
							/* @__PURE__ */ jsx(CountUp, {
								value: sightingsCount,
								className: "font-display text-xl font-extrabold"
							}),
							" ",
							t.welcome.sightingsValue(sightingsCount)
						] }),
						href: "/mapa"
					}, "relatos"),
					/* @__PURE__ */ jsx(InfoCard, {
						label: t.welcome.runway,
						value: t.welcome.runwayValue,
						extra: /* @__PURE__ */ jsx(Badge, {
							tone: "car",
							children: t.welcome.planning
						})
					}, "pista")
				].map((card, i) => /* @__PURE__ */ jsx("div", {
					className: `relative p-5 sm:p-6 ${i % 2 === 1 ? "border-l-2 border-dashed border-night/15" : ""} ${i >= 2 ? "border-t-2 border-dashed border-night/15 md:border-t-0" : ""} ${i === 2 ? "md:border-l-2" : ""}`,
					children: card
				}, i))
			})
		})]
	});
}
//#endregion
//#region resources/js/Pages/Home.tsx
function Home({ counters, content, sightings, products, spaces, partners }) {
	return /* @__PURE__ */ jsxs(Fragment, { children: [
		/* @__PURE__ */ jsx(SeoHead, {}),
		/* @__PURE__ */ jsx(AbductionHero, {}),
		/* @__PURE__ */ jsx(WelcomeSection, {
			intro: content.home_intro,
			sightingsCount: counters.sightings
		}),
		/* @__PURE__ */ jsx(LogbookSection, { sightings }),
		/* @__PURE__ */ jsx(Marquee, { items: t.strip }),
		/* @__PURE__ */ jsx(PlaceSection, {
			lead: content.home_place,
			spaces
		}),
		/* @__PURE__ */ jsx(SouvenirsSection, {
			lead: content.home_store,
			products
		}),
		/* @__PURE__ */ jsx(LegendSection, {
			teaser: content.home_legend,
			pending: content.legend_body === null
		}),
		/* @__PURE__ */ jsx(RegionSection, {
			partners,
			contactEmail: content.contact_email
		}),
		/* @__PURE__ */ jsx(CommunitySection, {
			whatsapp: content.link_whatsapp,
			instagram: content.link_instagram
		})
	] });
}
Home.layout = (page) => /* @__PURE__ */ jsx(PublicLayout, { children: page });
//#endregion
export { Home as default };
