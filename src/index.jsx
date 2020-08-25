import CloneReplacePluginInfo from './cloneReplaceInfo';

const {
  plugins: {
    registerPlugin,
  },
} = wp;

registerPlugin('clone-replace-info', { render: CloneReplacePluginInfo });
