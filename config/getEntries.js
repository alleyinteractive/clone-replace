/**
 * Prepend app entries with hot entries for development mode.
 *
 * @param  {string} mode The webpack mode.
 * @param {object} entries The app entries.
 * @return {object}      The app entries.
 */
module.exports = function getEntries(mode, entries) {
  if (mode === 'production') {
    return entries;
  }

  const { PROXY_URL = 'https://0.0.0.0:8080' } = process.env;

  const hotEntries = [
    'react-hot-loader/patch',
    `webpack-dev-server/client?${PROXY_URL}`,
    'webpack/hot/only-dev-server',
  ];

  return Object.keys(entries).reduce((acc, entryKey) => {
    const entry = Array.isArray(entries[entryKey])
      ? entries[entryKey]
      : [entries[entryKey]];

    return {
      ...acc,
      [entryKey]: [...hotEntries, ...entry],
    };
  }, {});
};
