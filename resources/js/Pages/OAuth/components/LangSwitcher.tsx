import { useEffect, useMemo, useState } from "react";
import { router, usePage } from "@inertiajs/react";
import { Select, SelectChangeEvent, MenuItem } from "@mui/material";
import { alpha } from "@mui/material/styles";
import "/node_modules/flag-icons/css/flag-icons.min.css";

export default function LangSwitcher() {
  type Lang = "en" | "de" | "es";

  const page = usePage();

  // Derive current locale from query (?language=xx) or from <html lang="xx">
  const initialLocale: Lang = useMemo(() => {
    try {
      const [_, qs = ""] = (page as any).url?.split("?") ?? ["", ""];
      const params = new URLSearchParams(qs);
      const qp = params.get("language") as Lang | null;
      if (qp === "en" || qp === "de" || qp === "es") return qp;
    } catch {}
    const htmlLang = typeof document !== "undefined" ? document.documentElement.lang : "en";
    const short = (htmlLang || "en").slice(0, 2) as Lang;
    return ["en", "de", "es"].includes(short) ? short : "en";
  }, [page]);

  const [locale, setLocale] = useState<Lang>(initialLocale);

  useEffect(() => {
    setLocale(initialLocale);
  }, [initialLocale]);

  const langs: Lang[] = ["en", "de", "es"];

  // Switch the language via Inertia (preserve path & query)
  const handleSwitchLanguage = (event: SelectChangeEvent<string>) => {
    const newLocale = event.target.value as Lang;
    if (newLocale === locale) return;
    setLocale(newLocale);

    const currentUrl: string = (page as any).url || window.location.pathname + window.location.search;
    const [pathname, qs = ""] = currentUrl.split("?");
    const params = new URLSearchParams(qs);
    params.set("language", newLocale);
    const newUrl = `${pathname}?${params.toString()}`;

    router.visit(newUrl, { preserveScroll: true, preserveState: true, replace: true });
  };

  return (
    <Select
      value={locale}
      inputProps={{
        IconComponent: () => null,
        sx: {
          padding: "0px",
          "&&.MuiSelect-select": {
            paddingBlock: "8px",
            paddingInline: "12px",
          },
        },
      }}
      onChange={handleSwitchLanguage}
      sx={{
        backgroundColor: alpha("#fff", 0.1),
      }}
    >
      {langs.map((lang) => (
        <MenuItem key={lang} value={lang}>
          {/* Use flag-icons CSS: en -> gb flag as common convention */}
          <span
            className={`fi fi-${lang === "en" ? "gb" : lang}`}
            style={{ display: "inline-block", width: 20, height: 14 }}
            aria-label={lang}
          />
        </MenuItem>
      ))}
    </Select>
  );
}
