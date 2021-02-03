/* global wp, React */

const {
  plugins: {
    registerPlugin,
  },
  editPost: {
    PluginPostStatusInfo,
  },
} = wp;

const CloneReplaceStatusInfo = () => (
  <PluginPostStatusInfo>
    <div>Post stutus info</div>
  </PluginPostStatusInfo>
);

registerPlugin('clone-replace-pre-publish-panel-test', {
  render: CloneReplaceStatusInfo,
});

