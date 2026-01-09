import resolve from '@rollup/plugin-node-resolve';
import typescript from '@rollup/plugin-typescript';
import terser from '@rollup/plugin-terser';
import dts from 'rollup-plugin-dts';

const production = !process.env.ROLLUP_WATCH;

export default [
  // Main bundle (IIFE for browser, ESM for bundlers)
  {
    input: 'src/js/index.ts',
    output: [
      {
        file: 'dist/consentpro.min.js',
        format: 'iife',
        name: 'ConsentPro',
        sourcemap: !production,
      },
      {
        file: 'dist/consentpro.esm.js',
        format: 'es',
        sourcemap: !production,
      },
    ],
    plugins: [
      resolve(),
      typescript({
        tsconfig: './tsconfig.json',
        declaration: false,
        sourceMap: !production,
      }),
      production &&
        terser({
          compress: {
            drop_console: true,
            drop_debugger: true,
            pure_funcs: ['console.log'],
          },
          mangle: {
            properties: {
              regex: /^_/,
            },
          },
          format: {
            comments: false,
          },
        }),
    ].filter(Boolean),
  },
  // TypeScript declarations
  {
    input: 'src/js/index.ts',
    output: {
      file: 'dist/consentpro.d.ts',
      format: 'es',
    },
    plugins: [dts()],
  },
];
