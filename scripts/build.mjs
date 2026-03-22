import { existsSync } from 'node:fs';
import * as esbuild from 'esbuild';

const isWatch = process.argv.includes('--watch');
const entryPoints = {};

if (existsSync('assets/src/admin/index.js')) {
  entryPoints.admin = 'assets/src/admin/index.js';
}

if (existsSync('assets/src/frontend/index.js')) {
  entryPoints.frontend = 'assets/src/frontend/index.js';
}

if (existsSync('assets/src/single/index.js')) {
  entryPoints.single = 'assets/src/single/index.js';
}

if (0 === Object.keys(entryPoints).length) {
  console.log('No asset entry points found.');
  process.exit(0);
}

const buildOptions = {
  entryPoints,
  bundle: true,
  minify: !isWatch,
  sourcemap: isWatch,
  outdir: 'assets/dist',
  target: ['es2020'],
  loader: {
    '.css': 'css',
  },
};

if (isWatch) {
  const context = await esbuild.context(buildOptions);
  await context.watch();
  console.log('Watching for changes...');
} else {
  await esbuild.build(buildOptions);
  console.log('Build complete.');
}
