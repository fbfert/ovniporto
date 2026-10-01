import { createInertiaApp } from "@inertiajs/react";
import createServer from "@inertiajs/react/server";
import { renderToString } from "react-dom/server";
import { jsx } from "react/jsx-runtime";
//#region resources/js/lib/pages.ts
var pages = /* #__PURE__ */ Object.assign({
	"../Pages/ComingSoon.tsx": () => import("./assets/ComingSoon-DSoPPdBT.js"),
	"../Pages/Dev/Styleguide.tsx": () => import("./assets/Styleguide-CkDVJ3zV.js"),
	"../Pages/Errors/Error.tsx": () => import("./assets/Error-D_uFd3-i.js"),
	"../Pages/Home.tsx": () => import("./assets/Home-Bihemd0E.js"),
	"../Pages/Waitlist/Confirmed.tsx": () => import("./assets/Confirmed-Bt7VLVdX.js")
});
async function resolvePage(name) {
	const loader = pages[`../Pages/${name}.tsx`];
	if (!loader) throw new Error(`Page not found: ${name}`);
	return (await loader()).default;
}
//#endregion
//#region resources/js/ssr.tsx
createServer((page) => createInertiaApp({
	page,
	render: renderToString,
	title: (title) => title ? `${title} · OVNIPORTO Lages` : "OVNIPORTO Lages",
	resolve: resolvePage,
	setup: ({ App, props }) => /* @__PURE__ */ jsx(App, { ...props })
}));
//#endregion
export {};
