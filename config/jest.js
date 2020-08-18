module.exports = {
  moduleDirectories: ['node_modules'],
  moduleFileExtensions: ['js', 'jsx'],
  moduleNameMapper: {
    // eslint-disable-next-line max-len
    '\\.(jpg|jpeg|png|gif|eot|otf|webp|svg|ttf|woff|woff2|mp4|webm|wav|mp3|m4a|aac|oga)$': '<rootDir>/mocks/fileMock.js',
    '\\.(css|less|scss)$': 'identity-obj-proxy',
  },
  modulePaths: ['<rootDir>'],
  rootDir: '..',
  setupFiles: [],
  setupFilesAfterEnv: [
    '<rootDir>/config/configureEnzyme.js',
    '<rootDir>/node_modules/jest-enzyme/lib/index.js',
  ],
  testMatch: [
    '<rootDir>/components/**/?(*.)+(spec|test).[jt]s?(x)',
    '<rootDir>/services/**/?(*.)+(spec|test).[jt]s?(x)',
    '<rootDir>/utils/**/?(*.)+(spec|test).[jt]s?(x)',
  ],
  transformIgnorePatterns: [
    '/node_modules/(?!aria-components).+(js|jsx)$',
  ],
};
