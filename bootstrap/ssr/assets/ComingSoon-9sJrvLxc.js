import { f as t, n as Display, p as Button, r as Eyebrow, t as Badge } from "./Typography-BT6DGpEB.js";
import { t as WaitlistForm } from "./WaitlistForm-BgqhhoUs.js";
import { n as SeoHead, t as PublicLayout } from "./PublicLayout-BFbm0IB7.js";
import { t as NightBanner } from "./NightBanner-CIwp9U51.js";
import { Fragment, jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Pages/ComingSoon.tsx
/** Honest placeholder for menu destinations that later OpenSpec changes will build. */
function ComingSoon({ slug, title, eyebrow, description, phase }) {
	return /* @__PURE__ */ jsxs(Fragment, { children: [/* @__PURE__ */ jsx(SeoHead, {
		title,
		description
	}), /* @__PURE__ */ jsxs(NightBanner, { children: [
		/* @__PURE__ */ jsx(Eyebrow, {
			tone: "dark",
			children: eyebrow
		}),
		/* @__PURE__ */ jsx(Display, {
			as: "h1",
			className: "mt-4 text-[clamp(2.4rem,1rem+6vw,6rem)]!",
			children: title
		}),
		/* @__PURE__ */ jsxs("div", {
			className: "mt-6 flex flex-wrap justify-center gap-2",
			children: [/* @__PURE__ */ jsx(Badge, {
				tone: "car",
				children: t.upcoming.badge
			}), /* @__PURE__ */ jsx(Badge, {
				tone: "neutral",
				children: phase
			})]
		}),
		/* @__PURE__ */ jsx("p", {
			className: "mx-auto mt-8 max-w-[52ch] text-lg leading-relaxed text-moonlight/80",
			children: description
		}),
		/* @__PURE__ */ jsxs("div", {
			className: "mx-auto mt-10 max-w-lg text-left",
			children: [/* @__PURE__ */ jsx("p", {
				className: "mb-3 text-center font-script text-2xl text-beam-glow",
				children: t.upcoming.notify
			}), /* @__PURE__ */ jsx(WaitlistForm, { source: slug })]
		}),
		/* @__PURE__ */ jsx("div", {
			className: "mt-10",
			children: /* @__PURE__ */ jsx(Button, {
				href: "/",
				variant: "secondary",
				tone: "dark",
				children: t.upcoming.back
			})
		})
	] })] });
}
ComingSoon.layout = (page) => /* @__PURE__ */ jsx(PublicLayout, { children: page });
//#endregion
export { ComingSoon as default };
