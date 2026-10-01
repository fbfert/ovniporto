import { Link } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
import { useEffect, useRef } from "react";
//#region resources/js/Components/Ui/Button.tsx
var base = "group/btn relative inline-flex min-h-11 items-center justify-center gap-2 rounded-full font-sans font-semibold tracking-[0.01em] whitespace-nowrap select-none transition-[transform,background-color,color,box-shadow,border-color] duration-200 ease-snap active:scale-[0.97] disabled:pointer-events-none disabled:opacity-50 aria-disabled:pointer-events-none aria-disabled:opacity-50";
var sizes = {
	sm: "h-11 px-4 text-sm",
	md: "h-12 px-6 text-[0.95rem]",
	lg: "h-14 px-8 text-base"
};
function variantClass(variant, tone) {
	switch (variant) {
		case "primary": return "bg-beam text-night [@media(hover:hover)]:hover:scale-[1.02] [@media(hover:hover)]:hover:shadow-beam";
		case "car": return "bg-car text-night [@media(hover:hover)]:hover:scale-[1.02] [@media(hover:hover)]:hover:shadow-[0_8px_28px_-8px_rgb(252_184_2/0.6)]";
		case "secondary": return tone === "dark" ? "border-[1.5px] border-moonlight/70 text-moonlight [@media(hover:hover)]:hover:border-moonlight [@media(hover:hover)]:hover:bg-moonlight/8" : "border-[1.5px] border-night/70 text-night [@media(hover:hover)]:hover:border-night [@media(hover:hover)]:hover:bg-night/5";
		case "ghost": return `px-1! ${tone === "dark" ? "text-moonlight" : "text-night"}`;
	}
}
function Content({ variant, loading, iconLeft, iconRight, children }) {
	return /* @__PURE__ */ jsxs(Fragment, { children: [
		loading ? /* @__PURE__ */ jsx("span", {
			"aria-hidden": true,
			className: "size-4 animate-spin rounded-full border-2 border-current border-r-transparent [animation-duration:600ms]"
		}) : iconLeft,
		/* @__PURE__ */ jsxs("span", {
			className: variant === "ghost" ? "relative" : void 0,
			children: [children, variant === "ghost" && /* @__PURE__ */ jsx("span", {
				"aria-hidden": true,
				className: "absolute -bottom-0.5 left-0 h-[1.5px] w-full origin-left scale-x-[0.35] bg-current transition-transform duration-300 ease-snap group-hover/btn:scale-x-100 group-focus-visible/btn:scale-x-100"
			})]
		}),
		iconRight && /* @__PURE__ */ jsx("span", {
			className: "transition-transform duration-200 ease-snap [@media(hover:hover)]:group-hover/btn:translate-x-0.5",
			children: iconRight
		})
	] });
}
function Button(props) {
	const { variant = "primary", size = "md", tone = "light", className = "" } = props;
	const classes = `${base} ${sizes[size]} ${variantClass(variant, tone)} ${className}`;
	if (props.href !== void 0) {
		const { href, external, onClick } = props;
		if (external || /^(https?:|mailto:|tel:)/.test(href)) return /* @__PURE__ */ jsx("a", {
			href,
			className: classes,
			onClick,
			...href.startsWith("http") ? {
				target: "_blank",
				rel: "noopener noreferrer"
			} : {},
			children: /* @__PURE__ */ jsx(Content, { ...props })
		});
		return /* @__PURE__ */ jsx(Link, {
			href,
			className: classes,
			onClick,
			prefetch: true,
			children: /* @__PURE__ */ jsx(Content, { ...props })
		});
	}
	const { variant: _v, size: _s, tone: _t, iconLeft: _l, iconRight: _r, loading, className: _c, children: _ch, href: _h, ...rest } = props;
	return /* @__PURE__ */ jsx("button", {
		type: "button",
		...rest,
		className: classes,
		"aria-busy": loading || void 0,
		disabled: rest.disabled || loading,
		children: /* @__PURE__ */ jsx(Content, { ...props })
	});
}
//#endregion
//#region resources/js/i18n/pt-BR.ts
/** Every interface string lives here. Editable page copy comes from content_blocks instead. */
var t = {
	brand: {
		name: "OVNIPORTO",
		tagline: "A pista de pouso do planalto",
		city: "Lages · SC",
		signoff: "Guardei um lugar pra você.",
		location: "Ao lado da Hospedaria Vila das Pedras · Lages, SC"
	},
	nav: {
		region: "Conheça a região",
		place: "O lugar",
		logbook: "Livro de avistamentos",
		store: "Loja",
		join: "Entrar na comunidade",
		home: "OVNIPORTO, voltar ao início",
		openMenu: "Abrir menu",
		closeMenu: "Fechar menu",
		skip: "Ir para o conteúdo",
		primary: "Navegação principal"
	},
	footer: {
		explore: "Navegue",
		community: "Comunidade",
		map: "Ver no mapa",
		privacy: "Privacidade",
		terms: "Termos",
		madeBy: "Feito na serra por",
		soon: "em breve"
	},
	hero: {
		freeVigil: "Vigília grátis",
		scroll: "Role para explorar",
		punchline: "Mais um pro Livro de avistamentos.",
		sceneLabel: "Ilustração: a serra de Lages à noite, com araucárias no horizonte. Um disco voador desce e abduz um carro amarelo."
	},
	welcome: {
		eyebrow: "Bem-vindo ao",
		where: "Onde",
		whereValue: "Vila das Pedras, Lages · SC",
		city: "Cidade",
		cityValue: "Lages, Santa Catarina",
		sightings: "Relatos",
		sightingsValue: (n) => n === 1 ? "no Livro" : "no Livro",
		runway: "Pista",
		runwayValue: "Meta 2028",
		planning: "em planejamento"
	},
	logbook: {
		eyebrow: "Alguém jurou ter visto",
		title: "Livro de avistamentos",
		lead: "Os últimos relatos aprovados pela torre de controle.",
		yourReport: "Seu relato aqui",
		report: "Relatar avistamento",
		map: "Ver o mapa",
		types: {
			light: "Luz",
			object: "Objeto",
			trail: "Rastro",
			other: "Outro"
		},
		noPlace: "Serra catarinense"
	},
	strip: [
		"A pista de pouso do planalto",
		"Lages · SC",
		"Meta 2028",
		"Vigília grátis"
	],
	place: {
		eyebrow: "Em planejamento",
		title: "O lugar",
		today: "O terreno hoje",
		todayEmpty: "Fotos do terreno em breve",
		future: "Como vai ficar",
		concept: "conceito",
		phase: (n) => `fase ${n}`,
		status: {
			planning: "em planejamento",
			building: "em obra",
			open: "aberto"
		},
		more: "Conhecer o projeto",
		spacesLabel: "Os espaços, fase a fase"
	},
	store: {
		eyebrow: "Lembranças de",
		title: "OVNIPORTO",
		cta: "Ver a loja",
		ready: "pronta entrega",
		madeToOrder: (days) => `feito sob pedido · ${days} dias`,
		empty: "A loja está separando as primeiras lembranças."
	},
	legend: {
		eyebrow: "De Cachi a Lages",
		title: "A lenda do carro amarelo",
		cta: "Ler a lenda",
		pending: "aguardando conteúdo",
		polaroid: "Essa história ainda está sendo escrita."
	},
	region: {
		eyebrow: "Fique mais um dia",
		title: "Conheça a região",
		empty: "Pousadas, trilhas e produtores da serra em breve.",
		join: "Quero aparecer aqui",
		all: "Ver tudo",
		example: "exemplo",
		types: {
			inn: "Pousada",
			attraction: "Passeio",
			producer: "Produtor",
			restaurant: "Comida",
			other: "Outro"
		}
	},
	community: {
		eyebrow: "Entre na vigília",
		title: "Comunidade",
		google: "Entrar com Google",
		whatsapp: "WhatsApp",
		instagram: "Instagram",
		linkSoon: "link em breve",
		waitlistTitle: "Avise-me da campanha",
		waitlistLead: "A arrecadação para a pista só abre quando o orçamento estiver pronto. Deixe o e-mail e a torre avisa.",
		emailLabel: "Seu e-mail",
		emailPlaceholder: "voce@exemplo.com",
		consent: "Quero receber um e-mail quando a campanha abrir. Posso sair quando quiser.",
		submit: "Me avise",
		sending: "Enviando…",
		postcardTitle: "Mande um postal",
		postcardLead: "Conhece alguém que precisa ver o céu de Lages?",
		postcardTo: "Para: quem precisa de um céu escuro",
		postcardMessage: "Achei o lugar onde o céu ainda é escuro e alguém sempre jura ter visto alguma coisa.",
		shareWhatsapp: "Enviar no WhatsApp",
		copy: "Copiar link",
		copied: "Link copiado",
		shareText: (url) => `Olha o que estão fazendo no céu de Lages: ${url}`
	},
	upcoming: {
		badge: "em construção pela torre",
		back: "Voltar ao início",
		notify: "Enquanto isso, deixe o e-mail:"
	},
	errors: {
		404: {
			title: "Esse ponto do céu ainda não foi mapeado.",
			lead: "O endereço não existe ou mudou de órbita."
		},
		403: {
			title: "Área restrita da torre.",
			lead: "Você não tem acesso a esta página."
		},
		419: {
			title: "A página ficou tempo demais no ar.",
			lead: "Recarregue e tente de novo."
		},
		429: {
			title: "Calma, piloto.",
			lead: "Muitas tentativas seguidas. Espere um minuto e tente de novo."
		},
		500: {
			title: "Perdemos o sinal da torre.",
			lead: "Algo deu errado do nosso lado. Já estamos olhando."
		},
		503: {
			title: "Pista em manutenção.",
			lead: "Voltamos em instantes."
		},
		back: "Voltar ao início"
	},
	waitlist: {
		confirmedEyebrow: "Torre de controle confirma",
		confirmedTitle: "Pronto.",
		confirmedLead: "Guardei um lugar pra você. Quando a campanha abrir, você fica sabendo antes."
	},
	toast: { close: "Fechar aviso" }
};
var money = (cents) => (cents / 100).toLocaleString("pt-BR", {
	style: "currency",
	currency: "BRL"
});
var shortDate = (iso) => {
	const [y, m, d] = iso.split("-").map(Number);
	return new Date(Date.UTC(y ?? 1970, (m ?? 1) - 1, d ?? 1)).toLocaleDateString("pt-BR", {
		day: "2-digit",
		month: "short",
		timeZone: "UTC"
	}).replace(".", "");
};
//#endregion
//#region resources/js/Components/Scene/Art.tsx
/**
* Hand-drawn SVG primitives for the OVNIPORTO night scene. Every shape is drawn
* around its own origin so it can be placed and animated inside any <svg>.
* Colors are brand tokens only.
*/
var C = {
	moonlight: "var(--color-moonlight)",
	night: "var(--color-night)",
	nightBlue: "var(--color-night-blue)",
	horizon: "var(--color-horizon)",
	beam: "var(--color-beam)",
	beamGlow: "var(--color-beam-glow)",
	car: "var(--color-car)"
};
/** The yellow car of the legend: a round-backed '70s beetle, side view. Origin = ground, center. Width ≈ 140. */
function YellowCarShape({ headlights = false }) {
	return /* @__PURE__ */ jsxs("g", { children: [
		headlights && /* @__PURE__ */ jsx("path", {
			d: "M66 -24 L150 -44 L150 -2 Z",
			fill: C.car,
			opacity: .18,
			style: { mixBlendMode: "screen" }
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-68 -12 C-70 -26 -60 -32 -46 -34 C-38 -54 -18 -64 4 -64 C28 -64 44 -54 52 -38 C62 -36 70 -30 70 -18 L70 -12 Z",
			fill: C.car
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-20 -58 C-8 -62 10 -62 22 -58",
			stroke: C.moonlight,
			strokeOpacity: .55,
			strokeWidth: 2.5,
			fill: "none",
			strokeLinecap: "round"
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-34 -38 C-26 -52 -12 -57 2 -57 L2 -38 Z",
			fill: C.nightBlue
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M8 -57 C24 -56 36 -50 43 -38 L8 -38 Z",
			fill: C.nightBlue
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-30 -42 L-22 -52",
			stroke: C.beamGlow,
			strokeOpacity: .35,
			strokeWidth: 2,
			strokeLinecap: "round"
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M5 -38 L5 -16",
			stroke: C.night,
			strokeOpacity: .35,
			strokeWidth: 1.5
		}),
		/* @__PURE__ */ jsx("rect", {
			x: 14,
			y: -33,
			width: 8,
			height: 2.4,
			rx: 1.2,
			fill: C.night,
			opacity: .5
		}),
		/* @__PURE__ */ jsx("rect", {
			x: -50,
			y: -14,
			width: 102,
			height: 4,
			rx: 2,
			fill: C.night,
			opacity: .35
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-60 -12 C-60 -30 -24 -30 -24 -12 Z",
			fill: C.car
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M24 -12 C24 -30 62 -30 62 -12 Z",
			fill: C.car
		}),
		/* @__PURE__ */ jsx("circle", {
			cx: -42,
			cy: -9,
			r: 12,
			fill: C.night
		}),
		/* @__PURE__ */ jsx("circle", {
			cx: 43,
			cy: -9,
			r: 12,
			fill: C.night
		}),
		/* @__PURE__ */ jsx("circle", {
			cx: -42,
			cy: -9,
			r: 4.5,
			fill: C.moonlight,
			opacity: .75
		}),
		/* @__PURE__ */ jsx("circle", {
			cx: 43,
			cy: -9,
			r: 4.5,
			fill: C.moonlight,
			opacity: .75
		}),
		/* @__PURE__ */ jsx("circle", {
			cx: 64,
			cy: -25,
			r: 3.6,
			fill: C.moonlight
		}),
		/* @__PURE__ */ jsx("rect", {
			x: -70,
			y: -26,
			width: 4,
			height: 6,
			rx: 1.5,
			fill: C.night,
			opacity: .6
		})
	] });
}
/** The saucer. Origin = center of the rim. Width ≈ 220. */
function SaucerShape({ lightsClassName = "" }) {
	return /* @__PURE__ */ jsxs("g", { children: [
		/* @__PURE__ */ jsx("ellipse", {
			cx: 0,
			cy: 16,
			rx: 46,
			ry: 9,
			fill: C.beam,
			opacity: .55
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-50 -6 C-46 -52 46 -52 50 -6 Z",
			fill: C.beamGlow,
			opacity: .9
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-36 -14 C-30 -40 4 -46 14 -40",
			stroke: C.moonlight,
			strokeOpacity: .8,
			strokeWidth: 3,
			fill: "none",
			strokeLinecap: "round"
		}),
		/* @__PURE__ */ jsx("ellipse", {
			cx: 0,
			cy: 0,
			rx: 110,
			ry: 22,
			fill: C.nightBlue
		}),
		/* @__PURE__ */ jsx("ellipse", {
			cx: 0,
			cy: -4,
			rx: 110,
			ry: 16,
			fill: C.moonlight,
			opacity: .92
		}),
		/* @__PURE__ */ jsx("ellipse", {
			cx: 0,
			cy: -6,
			rx: 78,
			ry: 9,
			fill: C.moonlight
		}),
		/* @__PURE__ */ jsx("path", {
			d: "M-110 -2 C-60 12 60 12 110 -2",
			stroke: C.night,
			strokeOpacity: .25,
			strokeWidth: 2,
			fill: "none"
		}),
		[
			-84,
			-56,
			-28,
			0,
			28,
			56,
			84
		].map((x, i) => /* @__PURE__ */ jsx("circle", {
			cx: x,
			cy: 8 - Math.abs(x) / 18,
			r: 4.2,
			fill: C.beam,
			className: lightsClassName,
			style: { animationDelay: `${i * 140}ms` }
		}, x))
	] });
}
/**
* Araucaria angustifolia silhouette: straight trunk and a flat, candelabra-shaped
* crown whose branches curve upward to tufted tips. Origin = base of the trunk.
*/
function AraucariaShape({ height, fill = C.night, seed = 1 }) {
	const h = height;
	const levels = 4;
	const branches = [];
	const tufts = [];
	const jitter = (n) => Math.sin(seed * 12.9898 + n * 78.233) * .5 + .5;
	for (let i = 0; i < levels; i++) {
		const y = -h * (.68 + i * .065);
		const span = h * (.4 - i * .075) * (.88 + jitter(i) * .24);
		const tipY = -h * (.955 + jitter(i + 10) * .035);
		for (const dir of [-1, 1]) {
			const tipX = dir * span;
			branches.push(`M0 ${y.toFixed(1)} C${(dir * span * .55).toFixed(1)} ${(y + h * .01).toFixed(1)} ${(tipX * .96).toFixed(1)} ${(y - h * .03).toFixed(1)} ${tipX.toFixed(1)} ${tipY.toFixed(1)}`);
			const size = h * (.105 - i * .012) * (.85 + jitter(i + 20) * .3);
			tufts.push({
				x: tipX,
				y: tipY,
				rx: size,
				ry: size * .42
			});
			tufts.push({
				x: tipX * .78,
				y: tipY + size * .35,
				rx: size * .7,
				ry: size * .32
			});
		}
	}
	const stroke = Math.max(1.6, h * .024);
	return /* @__PURE__ */ jsxs("g", { children: [
		/* @__PURE__ */ jsx("path", {
			d: `M-${h * .016} 0 L-${h * .009} ${-h * .97} L${h * .009} ${-h * .97} L${h * .016} 0 Z`,
			fill
		}),
		/* @__PURE__ */ jsx("path", {
			d: branches.join(" "),
			stroke: fill,
			strokeWidth: stroke,
			fill: "none",
			strokeLinecap: "round"
		}),
		tufts.map((t, i) => /* @__PURE__ */ jsx("ellipse", {
			cx: t.x,
			cy: t.y,
			rx: t.rx,
			ry: t.ry,
			fill
		}, i)),
		/* @__PURE__ */ jsx("ellipse", {
			cx: 0,
			cy: -h * .975,
			rx: h * .1,
			ry: h * .04,
			fill
		})
	] });
}
var SERRA = {
	far: "M0 760 C160 700 300 732 440 712 C600 690 700 742 860 722 C1020 702 1140 668 1300 690 C1440 708 1520 690 1600 700 L1600 1000 L0 1000 Z",
	near: "M0 822 C200 792 380 842 560 860 C680 871 740 873 800 873 C880 873 960 867 1080 851 C1260 827 1420 800 1600 812 L1600 1000 L0 1000 Z"
};
/** Placement of trees on the ridges (viewBox 1600×1000), tuned so phones still see a few. */
var ARAUCARIAS = {
	far: [
		{
			x: 300,
			y: 724,
			h: 74,
			seed: 3
		},
		{
			x: 470,
			y: 712,
			h: 92,
			seed: 5
		},
		{
			x: 700,
			y: 732,
			h: 64,
			seed: 7
		},
		{
			x: 935,
			y: 720,
			h: 86,
			seed: 11
		},
		{
			x: 1150,
			y: 682,
			h: 70,
			seed: 13
		},
		{
			x: 1360,
			y: 700,
			h: 96,
			seed: 17
		}
	],
	near: [
		{
			x: 120,
			y: 812,
			h: 300,
			seed: 2
		},
		{
			x: 290,
			y: 818,
			h: 200,
			seed: 4
		},
		{
			x: 610,
			y: 864,
			h: 118,
			seed: 6
		},
		{
			x: 1015,
			y: 858,
			h: 148,
			seed: 8
		},
		{
			x: 1250,
			y: 828,
			h: 250,
			seed: 10
		},
		{
			x: 1470,
			y: 806,
			h: 320,
			seed: 12
		}
	]
};
//#endregion
//#region resources/js/Components/Scene/Starfield.tsx
var BASE_COUNT = {
	low: 90,
	medium: 160
};
var FRAME_MS = 1e3 / 30;
var MAX_PARALLAX = 12;
var METEOR_MS = 750;
var MOONLIGHT = "244,245,232";
var BEAM_GLOW = "173,219,161";
function createStars(count) {
	return Array.from({ length: count }, () => {
		const layer = Math.random() < .6 ? 0 : Math.random() < .75 ? 1 : 2;
		return {
			x: Math.random(),
			y: Math.random(),
			layer,
			r: [
				.55,
				.9,
				1.35
			][layer] * (.75 + Math.random() * .5),
			alpha: [
				.35,
				.6,
				.9
			][layer] * (.7 + Math.random() * .3),
			speed: .4 + Math.random() * 1.4,
			phase: Math.random() * Math.PI * 2,
			tint: Math.random() < .1 ? BEAM_GLOW : MOONLIGHT
		};
	});
}
/**
* Night sky on a canvas: three depth layers, slow twinkle, a little scroll
* parallax and a shooting star every 8–15s. Draws at ~30fps only while visible
* and while the tab is in front; reduced motion gets a single still frame.
*/
function Starfield({ density = "medium", parallax = false, className = "" }) {
	const canvasRef = useRef(null);
	useEffect(() => {
		const canvas = canvasRef.current;
		const ctx = canvas?.getContext("2d");
		if (!canvas || !ctx) return;
		const reduced = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
		let width = 0;
		let height = 0;
		let stars = [];
		let meteor = null;
		let nextMeteorAt = performance.now() + 4e3 + Math.random() * 6e3;
		let raf = 0;
		let last = 0;
		let visible = false;
		const resize = () => {
			const rect = canvas.getBoundingClientRect();
			const dpr = Math.min(window.devicePixelRatio || 1, 2);
			width = rect.width;
			height = rect.height;
			canvas.width = Math.round(width * dpr);
			canvas.height = Math.round(height * dpr);
			ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
			const areaFactor = Math.min(1.15, Math.max(.45, width * height / 1296e3));
			stars = createStars(Math.round(BASE_COUNT[density] * areaFactor));
			draw(performance.now());
		};
		const parallaxOffset = () => {
			if (!parallax || reduced) return 0;
			const rect = canvas.getBoundingClientRect();
			return Math.max(-1, Math.min(1, rect.top / window.innerHeight)) * MAX_PARALLAX;
		};
		const drawMeteor = (now) => {
			if (!meteor) return;
			const t = (now - meteor.born) / METEOR_MS;
			if (t >= 1) {
				meteor = null;
				return;
			}
			const travel = meteor.length * 2.2 * t;
			const headX = meteor.x + Math.cos(meteor.angle) * travel;
			const headY = meteor.y + Math.sin(meteor.angle) * travel;
			const tailX = headX - Math.cos(meteor.angle) * meteor.length;
			const tailY = headY - Math.sin(meteor.angle) * meteor.length;
			const fade = Math.sin(Math.PI * t);
			const gradient = ctx.createLinearGradient(tailX, tailY, headX, headY);
			gradient.addColorStop(0, `rgba(${MOONLIGHT},0)`);
			gradient.addColorStop(1, `rgba(${MOONLIGHT},${.85 * fade})`);
			ctx.strokeStyle = gradient;
			ctx.lineWidth = 1.4;
			ctx.lineCap = "round";
			ctx.beginPath();
			ctx.moveTo(tailX, tailY);
			ctx.lineTo(headX, headY);
			ctx.stroke();
		};
		const draw = (now) => {
			ctx.clearRect(0, 0, width, height);
			const offset = parallaxOffset();
			const time = now / 1e3;
			for (const star of stars) {
				const twinkle = reduced ? 1 : .62 + .38 * Math.sin(time * star.speed + star.phase);
				const y = star.y * height + offset * (star.layer + 1) * .5;
				ctx.fillStyle = `rgba(${star.tint},${(star.alpha * twinkle).toFixed(3)})`;
				ctx.beginPath();
				ctx.arc(star.x * width, y, star.r, 0, Math.PI * 2);
				ctx.fill();
			}
			if (!reduced) drawMeteor(now);
		};
		const tick = (now) => {
			raf = requestAnimationFrame(tick);
			if (now - last < FRAME_MS) return;
			last = now;
			if (!meteor && now >= nextMeteorAt) {
				meteor = {
					x: width * (.15 + Math.random() * .6),
					y: height * (.05 + Math.random() * .35),
					born: now,
					angle: Math.PI * (.12 + Math.random() * .14),
					length: 70 + Math.random() * 90
				};
				nextMeteorAt = now + 8e3 + Math.random() * 7e3;
			}
			draw(now);
		};
		const start = () => {
			if (reduced || raf || !visible || document.hidden) return;
			raf = requestAnimationFrame(tick);
		};
		const stop = () => {
			cancelAnimationFrame(raf);
			raf = 0;
		};
		const observer = new IntersectionObserver(([entry]) => {
			visible = Boolean(entry?.isIntersecting);
			if (visible) start();
			else stop();
		});
		observer.observe(canvas);
		const onVisibility = () => document.hidden ? stop() : start();
		document.addEventListener("visibilitychange", onVisibility);
		const resizeObserver = new ResizeObserver(resize);
		resizeObserver.observe(canvas);
		return () => {
			stop();
			observer.disconnect();
			resizeObserver.disconnect();
			document.removeEventListener("visibilitychange", onVisibility);
		};
	}, [density, parallax]);
	return /* @__PURE__ */ jsx("canvas", {
		ref: canvasRef,
		"aria-hidden": true,
		className: `pointer-events-none h-full w-full ${className}`
	});
}
//#endregion
//#region resources/js/Components/Ui/Typography.tsx
/** Handwritten overline in Caveat, tilted like it was scribbled on the poster. */
function Eyebrow({ children, tone = "light", className = "", as: Tag = "p" }) {
	return /* @__PURE__ */ jsx(Tag, {
		className: `-rotate-2 font-script text-[clamp(1.5rem,1.2rem+1.2vw,2.1rem)] leading-none font-semibold ${tone === "dark" ? "text-beam-glow" : "text-horizon"} ${className}`,
		children
	});
}
var displaySizes = {
	h1: "text-[clamp(2rem,9.4vw,9rem)] leading-[0.92]",
	h2: "text-[clamp(1.6rem,0.6rem+4.4vw,4.6rem)] leading-[0.95]",
	h3: "text-[clamp(1.6rem,1rem+3vw,3.6rem)] leading-[1]"
};
/** Wide geometric display type, uppercase, as on the printed sticker. */
function Display({ as = "h2", children, outlined = false, className = "", id }) {
	const Tag = as;
	const size = as === "h1" || as === "h2" || as === "h3" ? displaySizes[as] : "";
	return /* @__PURE__ */ jsx(Tag, {
		id,
		className: `font-display font-extrabold tracking-[0.04em] uppercase ${size} ${outlined ? "text-outline" : ""} ${className}`,
		children
	});
}
var badgeTones = {
	beam: "bg-beam/15 text-night ring-beam/50 [[data-tone=dark]_&]:text-beam-glow",
	car: "bg-car text-night ring-night/10",
	horizon: "bg-horizon text-moonlight ring-moonlight/20",
	neutral: "bg-night/6 text-night/75 ring-night/15 [[data-tone=dark]_&]:bg-moonlight/10 [[data-tone=dark]_&]:text-moonlight/80 [[data-tone=dark]_&]:ring-moonlight/20"
};
function Badge({ children, tone = "neutral", className = "" }) {
	return /* @__PURE__ */ jsx("span", {
		className: `inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[0.72rem] leading-none font-semibold ring-1 ring-inset ${badgeTones[tone]} ${className}`,
		children
	});
}
//#endregion
export { ARAUCARIAS as a, SaucerShape as c, shortDate as d, t as f, Starfield as i, YellowCarShape as l, Display as n, AraucariaShape as o, Button as p, Eyebrow as r, SERRA as s, Badge as t, money as u };
