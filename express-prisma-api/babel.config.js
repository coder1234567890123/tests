export default {
  presets: [
    ['@babel/preset-env', { targets: { node: 'current' } }],
    // '@babel/preset-typescript', // Not needed if ts-jest handles TS transformation
  ],
};
