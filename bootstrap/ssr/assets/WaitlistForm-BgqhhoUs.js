import { f as t, p as Button } from "./Typography-BT6DGpEB.js";
import { n as TextField, t as CheckboxField } from "./Fields-CIxo4ubh.js";
import { useForm } from "@inertiajs/react";
import { jsx, jsxs } from "react/jsx-runtime";
//#region resources/js/Components/Home/WaitlistForm.tsx
/** "Avise-me da campanha": e-mail + explicit consent, double opt-in on the server. */
function WaitlistForm({ source = "home", tone = "dark" }) {
	const form = useForm({
		email: "",
		consent: false,
		source
	});
	const submit = (event) => {
		event.preventDefault();
		form.post("/avise-me", {
			preserveScroll: true,
			onSuccess: () => form.reset("email", "consent")
		});
	};
	return /* @__PURE__ */ jsxs("form", {
		onSubmit: submit,
		noValidate: true,
		className: "flex flex-col gap-3",
		children: [/* @__PURE__ */ jsxs("div", {
			className: "flex flex-col gap-3 sm:flex-row sm:items-start",
			children: [/* @__PURE__ */ jsx(TextField, {
				className: "flex-1",
				tone,
				label: t.community.emailLabel,
				hideLabel: true,
				type: "email",
				name: "email",
				autoComplete: "email",
				inputMode: "email",
				placeholder: t.community.emailPlaceholder,
				value: form.data.email,
				onChange: (e) => form.setData("email", e.target.value),
				error: form.errors.email,
				required: true
			}), /* @__PURE__ */ jsx(Button, {
				type: "submit",
				size: "md",
				className: "h-12 shrink-0",
				loading: form.processing,
				children: form.processing ? t.community.sending : t.community.submit
			})]
		}), /* @__PURE__ */ jsx(CheckboxField, {
			tone,
			name: "consent",
			checked: form.data.consent,
			onChange: (e) => form.setData("consent", e.target.checked),
			label: t.community.consent,
			error: form.errors.consent
		})]
	});
}
//#endregion
export { WaitlistForm as t };
