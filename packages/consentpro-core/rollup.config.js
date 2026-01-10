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
        sourcemap: true,
      },
      {
        file: 'dist/consentpro.esm.js',
        format: 'es',
        sourcemap: true,
      },
    ],
    plugins: [
      resolve(),
      typescript({
        tsconfig: './tsconfig.json',
        declaration: false,
        sourceMap: true,
      }),
      production &&
        terser({
          compress: {
            drop_console: true,
            drop_debugger: true,
            pure_funcs: ['console.log'],
            passes: 2,
          },
          mangle: {
            properties: {
              regex: /^_/,
            },
          },
          format: {
            comments: false,
          },
          sourceMap: true,
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
