/* eslint-disable no-underscore-dangle */
/* global React */
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { registerPlugin } from '@wordpress/plugins';
import ReplacePost from './components/replacePost';
import ClonePost from './components/clonePost';

/**
 * Slotfill to add clone & replace controls to the sidebar.
 */
const CloneReplaceStatusInfo = () => (
  <PluginPostStatusInfo>
    <ClonePost />
    <ReplacePost />
  </PluginPostStatusInfo>
);

registerPlugin('clone-replace-pre-publish-panel', {
  render: CloneReplaceStatusInfo,
});
