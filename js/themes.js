const themes = {
  // ── Indigo — kept as-is ───────────────────────────────────────────────────
  indigo: {
    "--color-primary": "#6366f1",
    "--color-primary-container": "#4f46e5",
    "--color-secondary": "#a855f7",
    "--color-secondary-container": "#f3e8ff",
    "--color-on-secondary-container": "#6b21a8",
    "--color-surface": "#ffffff",
    "--color-surface-bright": "#fafafa",
    "--color-surface-container": "#f8fafc",
    "--color-surface-container-low": "#f1f5f9",
    "--color-surface-container-high": "#e2e8f0",
    "--color-surface-container-highest": "#cbd5e1",
    "--color-on-surface": "#0f172a",
    "--color-on-surface-variant": "#475569",
    "--color-outline": "#94a3b8",
    "--color-outline-variant": "#e2e8f0",
    "--color-tertiary-fixed-dim": "#f59e0b",
    "--color-on-tertiary-fixed": "#451a03",
    "--color-error": "#ef4444",
    "--color-error-container": "#fee2e2",
  },

  // ── Brun — kept as-is ────────────────────────────────────────────────────
  brown: {
    "--color-primary": "#6c2f00",
    "--color-primary-container": "#8b4513",
    "--color-secondary": "#7c572d",
    "--color-secondary-container": "#fecb97",
    "--color-on-secondary-container": "#79542a",
    "--color-surface": "#fdf9f1",
    "--color-surface-bright": "#fdf9f1",
    "--color-surface-container": "#f1ede5",
    "--color-surface-container-low": "#f7f3eb",
    "--color-surface-container-high": "#ece8e0",
    "--color-surface-container-highest": "#e6e2da",
    "--color-on-surface": "#1c1c17",
    "--color-on-surface-variant": "#54433a",
    "--color-outline": "#877369",
    "--color-outline-variant": "#dac2b6",
    "--color-tertiary-fixed-dim": "#e7c446",
    "--color-on-tertiary-fixed": "#231b00",
    "--color-error": "#ba1a1a",
    "--color-error-container": "#ffdad6",
  },

  // ── Cendre — warm gray, timeless and neutral ──────────────────────────────
  cendre: {
    "--color-primary": "#44403c",
    "--color-primary-container": "#292524",
    "--color-secondary": "#78716c",
    "--color-secondary-container": "#e7e5e4",
    "--color-on-secondary-container": "#44403c",
    "--color-surface": "#fafaf9",
    "--color-surface-bright": "#ffffff",
    "--color-surface-container": "#f5f5f4",
    "--color-surface-container-low": "#fafaf9",
    "--color-surface-container-high": "#e7e5e4",
    "--color-surface-container-highest": "#d6d3d1",
    "--color-on-surface": "#1c1917",
    "--color-on-surface-variant": "#57534e",
    "--color-outline": "#a8a29e",
    "--color-outline-variant": "#e7e5e4",
    "--color-tertiary-fixed-dim": "#f59e0b",
    "--color-on-tertiary-fixed": "#451a03",
    "--color-error": "#dc2626",
    "--color-error-container": "#fee2e2",
  },

  // ── Mauve — deep warm violet, richer than indigo ──────────────────────────
  mauve: {
    "--color-primary": "#7c3aed",
    "--color-primary-container": "#6d28d9",
    "--color-secondary": "#a78bfa",
    "--color-secondary-container": "#ede9fe",
    "--color-on-secondary-container": "#4c1d95",
    "--color-surface": "#fdfcff",
    "--color-surface-bright": "#ffffff",
    "--color-surface-container": "#f5f3ff",
    "--color-surface-container-low": "#faf9ff",
    "--color-surface-container-high": "#ede9fe",
    "--color-surface-container-highest": "#ddd6fe",
    "--color-on-surface": "#1e1b4b",
    "--color-on-surface-variant": "#4c1d95",
    "--color-outline": "#a78bfa",
    "--color-outline-variant": "#ede9fe",
    "--color-tertiary-fixed-dim": "#fbbf24",
    "--color-on-tertiary-fixed": "#451a03",
    "--color-error": "#dc2626",
    "--color-error-container": "#fee2e2",
  },

  // ── Ambre — burnt orange, terra cotta warmth ─────────────────────────────
  ambre: {
    "--color-primary": "#c2410c",
    "--color-primary-container": "#9a3412",
    "--color-secondary": "#ea580c",
    "--color-secondary-container": "#fed7aa",
    "--color-on-secondary-container": "#9a3412",
    "--color-surface": "#fffbf7",
    "--color-surface-bright": "#ffffff",
    "--color-surface-container": "#fff7ed",
    "--color-surface-container-low": "#fffbf7",
    "--color-surface-container-high": "#fed7aa",
    "--color-surface-container-highest": "#fdba74",
    "--color-on-surface": "#1c0a00",
    "--color-on-surface-variant": "#7c2d12",
    "--color-outline": "#c2410c",
    "--color-outline-variant": "#fed7aa",
    "--color-tertiary-fixed-dim": "#fbbf24",
    "--color-on-tertiary-fixed": "#451a03",
    "--color-error": "#dc2626",
    "--color-error-container": "#fee2e2",
  },

  // ── Nuit — refined dark mode, indigo family ───────────────────────────────
  nuit: {
    "--color-primary": "#818cf8",
    "--color-primary-container": "#6366f1",
    "--color-secondary": "#a5b4fc",
    "--color-secondary-container": "#1e1b4b",
    "--color-on-secondary-container": "#c7d2fe",
    "--color-surface": "#0f0f1a",
    "--color-surface-bright": "#1e1b4b",
    "--color-surface-container": "#13131f",
    "--color-surface-container-low": "#1a1a2e",
    "--color-surface-container-high": "#1e293b",
    "--color-surface-container-highest": "#263148",
    "--color-on-surface": "#e2e8f0",
    "--color-on-surface-variant": "#94a3b8",
    "--color-outline": "#475569",
    "--color-outline-variant": "#1e293b",
    "--color-tertiary-fixed-dim": "#f59e0b",
    "--color-on-tertiary-fixed": "#1c1400",
    "--color-error": "#f87171",
    "--color-error-container": "#450a0a",
  },
};

function applyTheme(name) {
  const theme = themes[name];
  if (!theme) return;
  Object.entries(theme).forEach(([k, v]) => {
    document.documentElement.style.setProperty(k, v);
  });
  localStorage.setItem("theme", name);
}

(function () {
  const saved = localStorage.getItem("theme");
  if (saved && themes[saved]) applyTheme(saved);
})();