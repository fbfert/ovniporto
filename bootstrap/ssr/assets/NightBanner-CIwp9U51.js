import { a as ARAUCARIAS, i as Starfield, o as AraucariaShape, s as SERRA } from "./Typography-BT6DGpEB.js";
import { jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Components/Scene/NightBanner.tsx
/** Short night cover used by inner pages: sky, stars and the serra skyline at the bottom. */
function NightBanner({ children, tall = false }) {
	return /* @__PURE__ */ jsxs("section", {
		"data-tone": "dark",
		className: `relative overflow-hidden bg-night text-moonlight ${tall ? "min-h-svh" : "min-h-[78svh]"} flex items-center`,
		children: [
			/* @__PURE__ */ jsx("div", { className: "absolute inset-0 bg-[radial-gradient(120%_80%_at_50%_0%,var(--color-night-blue)_0%,var(--color-night)_62%)]" }),
			/* @__PURE__ */ jsx(Starfield, {
				className: "absolute inset-0",
				density: "medium"
			}),
			/* @__PURE__ */ jsx("div", { className: "absolute inset-x-0 bottom-0 h-1/2 bg-[linear-gradient(to_bottom,transparent,rgb(73_67_131/0.38))]" }),
			/* @__PURE__ */ jsxs("svg", {
				"aria-hidden": true,
				viewBox: "0 600 1600 400",
				preserveAspectRatio: "xMidYMax slice",
				className: "absolute inset-x-0 bottom-0 h-[38%] w-full",
				children: [
					/* @__PURE__ */ jsx("path", {
						d: SERRA.far,
						fill: "var(--color-night-blue)"
					}),
					/* @__PURE__ */ jsx("path", {
						d: SERRA.near,
						fill: "var(--color-night)"
					}),
					ARAUCARIAS.near.map((tree) => /* @__PURE__ */ jsx("g", {
						transform: `translate(${tree.x} ${tree.y})`,
						children: /* @__PURE__ */ jsx(AraucariaShape, {
							height: tree.h * .8,
							seed: tree.seed
						})
					}, tree.x))
				]
			}),
			/* @__PURE__ */ jsx("div", {
				className: "relative z-[2] mx-auto w-full max-w-4xl px-5 pt-32 pb-40 text-center sm:px-8",
				children
			})
		]
	});
}
//#endregion
export { NightBanner as t };
