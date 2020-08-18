import React from 'react';
import CloneButton from './cloneButton';
import ReplaceAction from './replaceAction';

const { registerPlugin } = wp.plugins;
const { PluginPostStatusInfo } = wp.editPost;

const { __ } = wp.i18n;
const { select } = wp.data;
const { isCurrentPostPublished } = select('core/editor');

const CloneReplacePluginInfo = () => (
  <PluginPostStatusInfo>
    <div id="clone-replace-actions">
      {!isCurrentPostPublished() && (
        <>
          <a href="#clone-replace-select">
            {__('Clone/Replace', 'clone-replace')}
          </a>
          <p>
            <a href="#clone-replace-select" className="save-clone-replace hide-if-no-js button">
              {__('OK', 'clone-replace')}
            </a>
            <a href="#clone-replace-select" className="cancel-clone-replace hide-if-no-js">
              {__('Cancel', 'clone-replace')}
            </a>
          </p>
        </>
      )}

      {isCurrentPostPublished() && (
        <>
          <CloneButton />
          <ReplaceAction />
        </>
      )}
    </div>
  </PluginPostStatusInfo>
);

registerPlugin('clone-replace-info', { render: CloneReplacePluginInfo });
