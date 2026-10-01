import { d as Seal, f as SealArt, n as useToast, t as ToastProvider, u as Section } from "./Toast-DbyDChDd.js";
import { n as Display, p as Button, r as Eyebrow, t as Badge } from "./Typography-BT6DGpEB.js";
import { n as TextField, t as CheckboxField } from "./Fields-CIxo4ubh.js";
import { a as InfoCard, i as Marquee, n as NightSkyArt, r as Polaroid, t as TicketCard } from "./TicketCard-Vqwu0LJq.js";
import { Head } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Pages/Dev/Styleguide.tsx
var swatches = [
	"moonlight",
	"night",
	"night-blue",
	"horizon",
	"beam",
	"beam-glow",
	"car"
];
function Row({ title, children }) {
	return /* @__PURE__ */ jsxs("div", {
		className: "border-t border-current/10 py-10",
		children: [/* @__PURE__ */ jsx("p", {
			className: "mb-6 text-[0.7rem] font-semibold tracking-[0.12em] uppercase opacity-60",
			children: title
		}), /* @__PURE__ */ jsx("div", {
			className: "flex flex-wrap items-center gap-4",
			children
		})]
	});
}
function ToastDemo() {
	const toast = useToast();
	return /* @__PURE__ */ jsx(Button, {
		variant: "secondary",
		tone: "dark",
		onClick: () => toast("Quase lá: confirme no seu e-mail."),
		children: "Mostrar toast"
	});
}
function Kit({ tone }) {
	return /* @__PURE__ */ jsxs(Fragment, { children: [
		/* @__PURE__ */ jsxs(Row, {
			title: "Botões",
			children: [
				/* @__PURE__ */ jsx(Button, { children: "Relatar avistamento" }),
				/* @__PURE__ */ jsx(Button, {
					variant: "secondary",
					tone,
					children: "Ver o mapa"
				}),
				/* @__PURE__ */ jsx(Button, {
					variant: "ghost",
					tone,
					children: "Ler a lenda"
				}),
				/* @__PURE__ */ jsx(Button, {
					variant: "car",
					children: "Ver a loja"
				}),
				/* @__PURE__ */ jsx(Button, {
					loading: true,
					children: "Enviando"
				}),
				/* @__PURE__ */ jsx(Button, {
					size: "sm",
					children: "Pequeno"
				}),
				/* @__PURE__ */ jsx(Button, {
					size: "lg",
					disabled: true,
					children: "Desabilitado"
				})
			]
		}),
		/* @__PURE__ */ jsx(Row, {
			title: "Tipografia",
			children: /* @__PURE__ */ jsxs("div", { children: [
				/* @__PURE__ */ jsx(Eyebrow, {
					tone,
					children: "Bem-vindo ao"
				}),
				/* @__PURE__ */ jsx(Display, {
					as: "h2",
					children: "OVNIPORTO"
				}),
				/* @__PURE__ */ jsx(Display, {
					as: "span",
					outlined: true,
					className: "text-6xl",
					children: "01"
				})
			] })
		}),
		/* @__PURE__ */ jsxs(Row, {
			title: "Etiquetas",
			children: [
				/* @__PURE__ */ jsx(Badge, {
					tone: "beam",
					children: "fase 1"
				}),
				/* @__PURE__ */ jsx(Badge, {
					tone: "car",
					children: "em planejamento"
				}),
				/* @__PURE__ */ jsx(Badge, {
					tone: "horizon",
					children: "conceito"
				}),
				/* @__PURE__ */ jsx(Badge, {
					tone: "neutral",
					children: "aguardando conteúdo"
				})
			]
		}),
		/* @__PURE__ */ jsxs(Row, {
			title: "Campos",
			children: [
				/* @__PURE__ */ jsx(TextField, {
					tone,
					label: "E-mail",
					placeholder: "voce@exemplo.com",
					className: "w-72"
				}),
				/* @__PURE__ */ jsx(TextField, {
					tone,
					label: "Com erro",
					defaultValue: "abc",
					error: "Esse e-mail não parece certo.",
					className: "w-72"
				}),
				/* @__PURE__ */ jsx(CheckboxField, {
					tone,
					label: "Autorizo publicar este relato."
				})
			]
		})
	] });
}
function Styleguide() {
	return /* @__PURE__ */ jsxs(ToastProvider, { children: [
		/* @__PURE__ */ jsx(Head, { title: "Styleguide" }),
		/* @__PURE__ */ jsxs(Section, {
			tone: "light",
			children: [
				/* @__PURE__ */ jsx(Display, {
					as: "h1",
					children: "Styleguide"
				}),
				/* @__PURE__ */ jsx(Row, {
					title: "Cores",
					children: swatches.map((name) => /* @__PURE__ */ jsxs("div", {
						className: "w-28",
						children: [/* @__PURE__ */ jsx("div", {
							className: "h-16 rounded-xl ring-1 ring-night/10",
							style: { background: `var(--color-${name})` }
						}), /* @__PURE__ */ jsx("p", {
							className: "mt-2 text-sm font-semibold",
							children: name
						})]
					}, name))
				}),
				/* @__PURE__ */ jsx(Kit, { tone: "light" }),
				/* @__PURE__ */ jsx(Row, {
					title: "Informação",
					children: /* @__PURE__ */ jsxs("dl", {
						className: "grid grid-cols-2 gap-6",
						children: [/* @__PURE__ */ jsx(InfoCard, {
							label: "Onde",
							value: "Vila das Pedras, Lages · SC"
						}), /* @__PURE__ */ jsx(InfoCard, {
							label: "Pista",
							value: "Meta 2028",
							extra: /* @__PURE__ */ jsx(Badge, {
								tone: "car",
								children: "em planejamento"
							})
						})]
					})
				}),
				/* @__PURE__ */ jsxs(Row, {
					title: "Polaroids",
					children: [/* @__PURE__ */ jsx("div", {
						className: "w-56",
						children: /* @__PURE__ */ jsx(Polaroid, {
							rotate: -4,
							art: /* @__PURE__ */ jsx(NightSkyArt, {
								label: "Luz",
								seed: 2
							}),
							caption: "Luz · Lages · 12 set"
						})
					}), /* @__PURE__ */ jsx("div", {
						className: "w-56",
						children: /* @__PURE__ */ jsx(Polaroid, {
							rotate: 3,
							tape: "corner",
							art: /* @__PURE__ */ jsx(NightSkyArt, { seed: 5 }),
							caption: "Seu relato aqui"
						})
					})]
				})
			]
		}),
		/* @__PURE__ */ jsx(Marquee, { items: [
			"A pista de pouso do planalto",
			"Lages · SC",
			"Meta 2028"
		] }),
		/* @__PURE__ */ jsxs(Section, {
			tone: "dark",
			pattern: "stars",
			wave: true,
			children: [
				/* @__PURE__ */ jsx(Kit, { tone: "dark" }),
				/* @__PURE__ */ jsxs(Row, {
					title: "Selo",
					children: [/* @__PURE__ */ jsx(Seal, { size: "sm" }), /* @__PURE__ */ jsx(Seal, {
						size: "md",
						glow: true
					})]
				}),
				/* @__PURE__ */ jsx(Row, {
					title: "Ingresso",
					children: /* @__PURE__ */ jsx("div", {
						className: "w-72",
						children: /* @__PURE__ */ jsx(TicketCard, {
							label: "PARA LEVAR",
							title: "Adesivo OVNIPORTO",
							meta: "pronta entrega",
							price: "R$ 8,00",
							art: /* @__PURE__ */ jsx("div", {
								className: "flex h-full items-center justify-center",
								children: /* @__PURE__ */ jsx("div", {
									className: "w-1/2",
									children: /* @__PURE__ */ jsx(SealArt, {})
								})
							})
						})
					})
				}),
				/* @__PURE__ */ jsx(Row, {
					title: "Toast",
					children: /* @__PURE__ */ jsx(ToastDemo, {})
				})
			]
		})
	] });
}
//#endregion
export { Styleguide as default };
