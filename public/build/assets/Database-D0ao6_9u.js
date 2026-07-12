import {
	_ as P
} from "./Button.vue_vue_type_script_setup_true_lang-DLbVc8FY.js";
import {
	_ as q
} from "./Switch.vue_vue_type_script_setup_true_lang-C7ZMvNjL.js";
import {
	_ as B,
	a as D
} from "./Alert.vue_vue_type_script_setup_true_lang-BJzj-emV.js";
import {
	_ as H
} from "./InputError-BbPAIupT.js";
import {
	_ as T
} from "./Input.vue_vue_type_script_setup_true_lang-BYzMHQE9.js";
import {
	_ as E
} from "./Label.vue_vue_type_script_setup_true_lang-BI4_AY6J.js";
import {
	T as z,
	q as I,
	Q as b,
	o as _,
	c as V,
	w as l,
	f as g,
	a,
	b as o,
	d as i,
	u as t,
	t as A,
	g as S,
	n as W,
	e as h,
	N as j
} from "./app-ografi-a978d3b.js";
import {
	_ as K
} from "./InstallerLayout-BZOiPsjO.js";
import {
	i as M
} from "./useThemeConfig-safe-82fea8c.js";
import "./index-T_RycVZ9.js";
import "./Link-BxabHujp.js";
import "./_plugin-vue_export-helper-DlAUqK2U.js";
const Q = {
		key: 0,
		class: "flex flex-col"
	},
	R = {
		class: "grid gap-4 mt-5"
	},
	F = {
		class: "grid gap-2"
	},
	G = {
		class: "grid gap-2"
	},
	J = {
		class: "grid gap-2"
	},
	X = {
		class: "grid gap-2"
	},
	Y = {
		class: "grid gap-2"
	},
	Z = {
		key: 1,
		class: "mb-3 flex gap-3"
	},
	ee = {
		key: 1,
		class: "flex flex-col"
	},
	se = {
		class: "grid gap-4 mt-5"
	},
	ce = {
		__name: "Database",
		setup(te) {
			var w, v, k;
			const s = z({
					db_host: "",
					db_port: "",
					db_name: "",
					db_user: "",
					db_password: "",
					db_overwrite_data: !1
				}),
				y = I(1),
				$ = (w = b().props) == null ? void 0 : w.api_url,
				U = (v = b().props) == null ? void 0 : v.api_key,
				N = (k = b().props) == null ? void 0 : k.license_key;
			async function C(p, e, d, m) {
				const n = new Headers;
				n.append("Content-Type", "application/x-www-form-urlencoded");
				const u = new URLSearchParams;
				u.append("api_key", e), u.append("license_key", d), u.append("identifier", m);
				const f = {
					method: "POST",
					headers: n,
					body: u,
					redirect: "follow"
				};
				try {
					return await (await fetch(p + "verify", f)).json()
				} catch {
					console.log("License server unavailable - please try again later")
				}
			}
			const L = async () => {
				try {
					const p = await C($, U, N, window.location.hostname),
						{
							response: {
								code: e,
								message: d
							}
						} = p;
					[100, 101, 102, 103, 210].includes(e) && toast.warning(d, {
						theme: M.value ? "dark" : "light"
					}), e === 150 && j.get(route("installer.index")), e === 200 && (y.value = !0)
				} catch {
					console.log("License server unavailable - please try again later")
				}
			}, O = () => {
				["db_host", "db_port", "db_name", "db_user", "db_password"].forEach(r => {
					const n = document.getElementById(r);
					n && (s[r] = n.value);
				}), s.post(route("installer.database.store"), {
					preserveState: !0,
					preserveScroll: !0
				})
			};
			return (p, e) => {
				const d = E,
					m = T,
					n = H,
					u = B,
					f = D,
					x = q,
					c = P;
				return _(), V(K, null, {
					default: l(() => [y.value ? (_(), g("div", Q, [e[13] || (e[13] = a("h1", {
						class: "text-left sm:text-center text-base sm:text-xl font-semibold"
					}, "Database Configuration", -1)), a("form", {
						onSubmit: h(O, ["prevent"])
					}, [a("div", R, [a("div", F, [o(d, {
						for: "db_host"
					}, {
						default: l(() => e[6] || (e[6] = [i("Host")])),
						_: 1
					}), o(m, {
						modelValue: t(s).db_host,
						"onUpdate:modelValue": e[0] || (e[0] = r => t(s).db_host = r),
						id: "db_host",
						type: "text",
						class: "block w-full",
						autofocus: "",
						placeholder: "localhost"
					}, null, 8, ["modelValue"]), o(n, {
						message: t(s).errors.db_host
					}, null, 8, ["message"])]), a("div", G, [o(d, {
						for: "db_port"
					}, {
						default: l(() => e[7] || (e[7] = [i("Port")])),
						_: 1
					}), o(m, {
						modelValue: t(s).db_port,
						"onUpdate:modelValue": e[1] || (e[1] = r => t(s).db_port = r),
						id: "db_port",
						type: "text",
						class: "block w-full",
						required: "",
						placeholder: "3306"
					}, null, 8, ["modelValue"]), o(n, {
						message: t(s).errors.db_port
					}, null, 8, ["message"])]), a("div", J, [o(d, {
						for: "db_name"
					}, {
						default: l(() => e[8] || (e[8] = [i("Name")])),
						_: 1
					}), o(m, {
						modelValue: t(s).db_name,
						"onUpdate:modelValue": e[2] || (e[2] = r => t(s).db_name = r),
						id: "db_name",
						type: "text",
						class: "block w-full",
						required: ""
					}, null, 8, ["modelValue"]), o(n, {
						message: t(s).errors.db_name
					}, null, 8, ["message"])]), a("div", X, [o(d, {
						for: "db_user"
					}, {
						default: l(() => e[9] || (e[9] = [i("User")])),
						_: 1
					}), o(m, {
						modelValue: t(s).db_user,
						"onUpdate:modelValue": e[3] || (e[3] = r => t(s).db_user = r),
						id: "db_user",
						type: "text",
						class: "block w-full",
						required: ""
					}, null, 8, ["modelValue"]), o(n, {
						message: t(s).errors.db_user
					}, null, 8, ["message"])]), a("div", Y, [o(d, {
						for: "db_password"
					}, {
						default: l(() => e[10] || (e[10] = [i("Password")])),
						_: 1
					}), o(m, {
						modelValue: t(s).db_password,
						"onUpdate:modelValue": e[4] || (e[4] = r => t(s).db_password = r),
						id: "db_password",
						type: "text",
						class: "block w-full"
					}, null, 8, ["modelValue"]), o(n, {
						message: t(s).errors.db_password
					}, null, 8, ["message"])]), p.$page.props.flash.db_alert ? (_(), V(f, {
						key: 0,
						variant: "warning"
					}, {
						default: l(() => [o(u, null, {
							default: l(() => [i(A(p.$page.props.flash.db_alert), 1)]),
							_: 1
						})]),
						_: 1
					})) : S("", !0), p.$page.props.flash.db_alert ? (_(), g("div", Z, [o(x, {
						checked: t(s).db_overwrite_data,
						"onUpdate:checked": e[5] || (e[5] = r => t(s).db_overwrite_data = r)
					}, null, 8, ["checked"]), e[11] || (e[11] = a("div", {
						class: "font-medium text-sm text-foreground"
					}, "I confirm that all data should be deleted and overwritten", -1))])) : S("", !0), o(c, {
						type: "submit",
						class: W({
							"opacity-25": t(s).processing
						}),
						disabled: t(s).processing
					}, {
						default: l(() => e[12] || (e[12] = [i(" Continue ")])),
						_: 1
					}, 8, ["class", "disabled"])])], 32)])) : (_(), g("div", ee, [e[15] || (e[15] = a("h1", {
						class: "text-left sm:text-center text-base sm:text-xl font-semibold"
					}, "Verify your license", -1)), a("form", {
						onSubmit: h(L, ["prevent"])
					}, [a("div", se, [o(c, {
						type: "submit"
					}, {
						default: l(() => e[14] || (e[14] = [i(" Verify ")])),
						_: 1
					})])], 32)]))]),
					_: 1
				})
			}
		}
	};
export {
	ce as
	default
};
