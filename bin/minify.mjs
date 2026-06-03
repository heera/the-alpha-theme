/**
 * Minify the theme's enqueued CSS/JS into `.min` twins.
 *
 * functions.php prefers `<file>.min.<ext>` at runtime (unless SCRIPT_DEBUG),
 * falling back to the source file when a minified version is absent — so the
 * source files stay the editable canonical copies and these outputs are purely
 * build artifacts.
 *
 * Usage: `npm run minify`  (or `node bin/minify.mjs`)
 */
import { readFile, writeFile } from "node:fs/promises";
import { fileURLToPath } from "node:url";
import { dirname, join } from "node:path";
import CleanCSS from "clean-css";
import { minify as terser } from "terser";

const ROOT = join(dirname(fileURLToPath(import.meta.url)), "..");

const CSS = [
  "assets/css/fonts.css",
  "assets/css/main.css",
  "assets/css/editor.css",
];
const JS = ["assets/js/theme.js"];

const kb = (n) => (n / 1024).toFixed(1) + " KB";
const pct = (from, to) => "-" + (((from - to) / from) * 100).toFixed(1) + "%";

const cleaner = new CleanCSS({ level: 2, returnPromise: true });

async function minifyCss(rel) {
  const src = await readFile(join(ROOT, rel), "utf8");
  const out = await cleaner.minify(src);
  if (out.errors.length) throw new Error(`${rel}: ${out.errors.join(", ")}`);
  const dest = rel.replace(/\.css$/, ".min.css");
  await writeFile(join(ROOT, dest), out.styles);
  console.log(
    `  css  ${rel}  ${kb(src.length)} → ${kb(out.styles.length)}  (${pct(src.length, out.styles.length)})`
  );
}

async function minifyJs(rel) {
  const src = await readFile(join(ROOT, rel), "utf8");
  const out = await terser(src, { compress: true, mangle: true });
  if (out.error) throw out.error;
  const dest = rel.replace(/\.js$/, ".min.js");
  await writeFile(join(ROOT, dest), out.code);
  console.log(
    `  js   ${rel}  ${kb(src.length)} → ${kb(out.code.length)}  (${pct(src.length, out.code.length)})`
  );
}

console.log("Minifying theme assets…");
await Promise.all([...CSS.map(minifyCss), ...JS.map(minifyJs)]);
console.log("Done.");
