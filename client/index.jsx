/* eslint-disable no-underscore-dangle */
/* global React */

import ReplacePost from './components/replacePost';

const {
  editPost: {
    PluginPostStatusInfo,
  },
  plugins: {
    registerPlugin,
  },
} = wp;

/**
 * Slotfill to add clone & replace controls to the sidebar.
 */
const CloneReplaceStatusInfo = () => (
  <PluginPostStatusInfo>
    <ReplacePost />
  </PluginPostStatusInfo>
);

registerPlugin('clone-replace-pre-publish-panel-test', {
  render: CloneReplaceStatusInfo,
});
