import React from 'react';

const {
  i18n: {
    __,
  },
  data: {
    select,
  },
  url: {
    addQueryArgs,
  },
} = wp;

const CloneAction = () => {
  const {
    getCurrentPostId,
    isCurrentPostPublished,
  } = select('core/editor');

  return (
    <div id="clone-action">
      {!isCurrentPostPublished() && (
        <h4 style={{ marginBottom: '0.33em' }}>
          {__('Clone', 'clone-replace')}
        </h4>
      )}
      <a
        href={addQueryArgs('admin-post.php', {
          action: 'clone_post',
          p: getCurrentPostId(),
          _wpnonce: cloneReplaceSettings.nonce,
        })}
        role="button"
      >
        {__('Clone to a new draft', 'clone-replace')}
      </a>
    </div>
  );
};

export default CloneAction;
