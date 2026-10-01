import { d as Seal } from "./Toast-DbyDChDd.js";
import { f as t, n as Display, p as Button, r as Eyebrow } from "./Typography-BT6DGpEB.js";
import { n as SeoHead, t as PublicLayout } from "./PublicLayout-BFbm0IB7.js";
import { t as NightBanner } from "./NightBanner-CIwp9U51.js";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Pages/Waitlist/Confirmed.tsx
function Confirmed() {
	return /* @__PURE__ */ jsxs(Fragment, { children: [/* @__PURE__ */ jsx(SeoHead, { title: "Inscrição confirmada" }), /* @__PURE__ */ jsxs(NightBanner, { children: [
		/* @__PURE__ */ jsx("div", {
			className: "flex justify-center",
			children: /* @__PURE__ */ jsx("div", {
				className: "animate-seal-in",
				children: /* @__PURE__ */ jsx(Seal, {
					size: "md",
					glow: true
				})
			})
		}),
		/* @__PURE__ */ jsx(Eyebrow, {
			tone: "dark",
			className: "mt-10",
			children: t.waitlist.confirmedEyebrow
		}),
		/* @__PURE__ */ jsx(Display, {
			as: "h1",
			className: "mt-3",
			children: t.waitlist.confirmedTitle
		}),
		/* @__PURE__ */ jsx("p", {
			className: "mx-auto mt-6 max-w-[42ch] text-lg text-moonlight/80",
			children: t.waitlist.confirmedLead
		}),
		/* @__PURE__ */ jsx("div", {
			className: "mt-10",
			children: /* @__PURE__ */ jsx(Button, {
				href: "/",
				children: t.upcoming.back
			})
		})
	] })] });
}
Confirmed.layout = (page) => /* @__PURE__ */ jsx(PublicLayout, { children: page });
//#endregion
export { Confirmed as default };
