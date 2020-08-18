import React from 'react';

const { __ } = wp.i18n;
const { select } = wp.data;
const {
  getCurrentPostId,
} = select('core/editor');
const { addQueryArgs } = wp.url;

const HeaderStyle = {
  marginBottom: '0.33em',
};

const CloneAction = () => (
  <div id="clone-action">
    <h4 style={HeaderStyle}>
      {__('Clone', 'clone-replace')}
    </h4>

    <a href={addQueryArgs('admin-post.php', {
      action: 'clone_post',
      p: getCurrentPostId(),
      _wpnonce: cloneReplaceSettings.nonce,
    })}
    >
      {__('Clone to a new draft', 'clone-replace')}
    </a>
  </div>
);

export default CloneAction;
