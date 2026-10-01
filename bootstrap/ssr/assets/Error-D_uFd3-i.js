import { f as t, l as YellowCarShape, n as Display, p as Button, r as Eyebrow } from "./Typography-DJJfLd3V.js";
import { t as NightBanner } from "./NightBanner-Cgur8goR.js";
import { Head } from "@inertiajs/react";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Pages/Errors/Error.tsx
/**
* Themed error page. Rendered outside the web middleware stack (404s never reach
* it), so it does not rely on shared props or the public layout.
*/
function Error({ status }) {
	const copy = t.errors[status] ?? t.errors[500];
	return /* @__PURE__ */ jsxs(Fragment, { children: [/* @__PURE__ */ jsx(Head, { title: `${status}` }), /* @__PURE__ */ jsx("main", { children: /* @__PURE__ */ jsxs(NightBanner, {
		tall: true,
		children: [
			/* @__PURE__ */ jsx("svg", {
				"aria-hidden": true,
				viewBox: "-90 -80 180 90",
				className: "mx-auto w-40 -rotate-[24deg] animate-hover-bob motion-reduce:animate-none",
				children: /* @__PURE__ */ jsx(YellowCarShape, { headlights: true })
			}),
			/* @__PURE__ */ jsxs(Eyebrow, {
				tone: "dark",
				className: "mt-8",
				children: ["Erro ", status]
			}),
			/* @__PURE__ */ jsx(Display, {
				as: "h1",
				className: "mx-auto mt-4 max-w-[18ch] text-[clamp(1.9rem,1rem+3.6vw,4rem)]!",
				children: copy.title
			}),
			/* @__PURE__ */ jsx("p", {
				className: "mx-auto mt-6 max-w-[44ch] text-lg text-moonlight/80",
				children: copy.lead
			}),
			/* @__PURE__ */ jsx("div", {
				className: "mt-10",
				children: /* @__PURE__ */ jsx(Button, {
					href: "/",
					size: "lg",
					children: t.errors.back
				})
			})
		]
	}) })] });
}
//#endregion
export { Error as default };
