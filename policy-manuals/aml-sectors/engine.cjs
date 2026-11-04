// Builds a sector variant of the AML/CFT/CPF manual from the base (gold and bullion) manual.
//
// The base parts in ../aml-cft-cpf are the single source of truth for everything that is the same
// in every sector. A sector file only lists what differs, as operations on the base source:
//   sub(file, old, new)          replace one exact piece of text (must match exactly once)
//   line(file, prefix, newLine)  replace the whole line that starts with `prefix` (null removes it)
//   block(file, from, to, text)  replace the lines from the one starting with `from` up to, but not
//                                including, the one starting with `to`
// Every operation must match exactly once, so if the base text changes the build stops and says
// which operation no longer fits, instead of silently producing a wrong manual.
const fs = require('fs');
const path = require('path');

const BASE = path.join(__dirname, '..', 'aml-cft-cpf');
const FILES = ['part1.cjs', 'part2.cjs', 'part3.cjs', 'part4.cjs'];

const Q = (t) => "'" + String(t).replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";

function apply(src, ops, sector) {
  for (const op of ops) {
    const [kind, file] = op;
    let text = src[file];
    if (text === undefined) throw new Error(sector + ': unknown file ' + file);
    if (kind === 'sub' || kind === 'subAll') {
      const [, , from, to] = op;
      const n = text.split(from).length - 1;
      if (kind === 'sub' && n !== 1) throw new Error(sector + ': sub matched ' + n + ' times in ' + file + ': ' + from.slice(0, 90));
      if (kind === 'subAll' && n < 1) throw new Error(sector + ': subAll matched nothing in ' + file + ': ' + from.slice(0, 90));
      src[file] = text.split(from).join(to);
      continue;
    }
    const lines = text.split('\n');
    if (kind === 'line') {
      const [, , prefix, to] = op;
      const hits = lines.map((l, i) => (l.trimStart().startsWith(prefix) ? i : -1)).filter((i) => i >= 0);
      if (hits.length !== 1) throw new Error(sector + ': line matched ' + hits.length + ' times in ' + file + ': ' + prefix.slice(0, 90));
      const i = hits[0];
      if (to === null) lines.splice(i, 1);
      else lines[i] = lines[i].match(/^\s*/)[0] + to;
      src[file] = lines.join('\n');
    } else if (kind === 'block') {
      const [, , from, to, replacement] = op;
      const a = lines.map((l, i) => (l.trimStart().startsWith(from) ? i : -1)).filter((i) => i >= 0);
      if (a.length !== 1) throw new Error(sector + ': block start matched ' + a.length + ' times in ' + file + ': ' + from.slice(0, 90));
      const b = lines.findIndex((l, i) => i > a[0] && l.trimStart().startsWith(to));
      if (b < 0) throw new Error(sector + ': block end not found in ' + file + ': ' + to.slice(0, 90));
      lines.splice(a[0], b - a[0], ...(replacement === null ? [] : String(replacement).split('\n')));
      src[file] = lines.join('\n');
    } else throw new Error(sector + ': unknown op ' + kind);
  }
}

// Writes the transformed parts to a temporary folder next to this file and returns it.
function transform(sector, ops) {
  const src = {};
  for (const f of FILES) src[f] = fs.readFileSync(path.join(BASE, f), 'utf8');
  apply(src, ops, sector);
  const dir = path.join(__dirname, '.build-' + sector);
  fs.mkdirSync(dir, { recursive: true });
  for (const f of FILES) fs.writeFileSync(path.join(dir, f), src[f]);
  fs.copyFileSync(path.join(BASE, 'lib.cjs'), path.join(dir, 'lib.cjs'));
  return dir;
}

module.exports = { transform, Q, BASE };
