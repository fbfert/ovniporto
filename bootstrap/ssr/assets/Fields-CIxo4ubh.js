import { jsx, jsxs } from "react/jsx-runtime";
import { useId } from "react";
//#region resources/js/Components/Ui/Fields.tsx
function TextField({ label, error, tone = "light", hideLabel = false, className = "", ...input }) {
	const id = useId();
	const errorId = `${id}-error`;
	return /* @__PURE__ */ jsxs("div", {
		className,
		children: [
			/* @__PURE__ */ jsx("label", {
				htmlFor: id,
				className: hideLabel ? "sr-only" : "mb-1.5 block text-sm font-semibold",
				children: label
			}),
			/* @__PURE__ */ jsx("input", {
				id,
				"aria-invalid": error ? true : void 0,
				"aria-describedby": error ? errorId : void 0,
				className: `h-12 w-full rounded-full border-[1.5px] px-5 text-base transition-colors duration-200 ease-snap outline-none ${tone === "dark" ? "bg-night/60 text-moonlight placeholder:text-moonlight/45 border-moonlight/25 focus:border-beam" : "bg-moonlight text-night placeholder:text-night/40 border-night/25 focus:border-horizon"} ${error ? "border-car!" : ""}`,
				...input
			}),
			error && /* @__PURE__ */ jsx("p", {
				id: errorId,
				className: `mt-2 pl-5 text-sm font-medium ${tone === "dark" ? "text-car" : "text-night"}`,
				children: error
			})
		]
	});
}
function CheckboxField({ label, error, tone = "light", ...input }) {
	const id = useId();
	return /* @__PURE__ */ jsxs("div", { children: [/* @__PURE__ */ jsxs("label", {
		htmlFor: id,
		className: "flex min-h-11 cursor-pointer items-start gap-3 py-1 text-sm leading-snug",
		children: [/* @__PURE__ */ jsx("input", {
			id,
			type: "checkbox",
			"aria-invalid": error ? true : void 0,
			className: `peer mt-0.5 size-5 shrink-0 cursor-pointer appearance-none rounded-md border-[1.5px] transition-colors duration-150 ease-snap checked:border-beam checked:bg-beam ${tone === "dark" ? "border-moonlight/50" : "border-night/50"} bg-[length:14px] bg-center bg-no-repeat checked:bg-[url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23061121' stroke-width='3.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M5 12.5l4.5 4.5L19 7.5'/%3E%3C/svg%3E")]`,
			...input
		}), /* @__PURE__ */ jsx("span", {
			className: tone === "dark" ? "text-moonlight/85" : "text-night/80",
			children: label
		})]
	}), error && /* @__PURE__ */ jsx("p", {
		className: `mt-1 pl-8 text-sm font-medium ${tone === "dark" ? "text-car" : "text-night"}`,
		children: error
	})] });
}
//#endregion
export { TextField as n, CheckboxField as t };
