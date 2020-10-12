import React, { useState } from 'react';
import PropTypes from 'prop-types';
import CloneButton from './cloneButton';
import PostSelector from './postSelector';

const {
  editPost: {
    PluginPostStatusInfo,
  },
  compose: {
    compose,
  },
  i18n: {
    __,
    sprintf,
  },
  data: {
    withSelect,
    withDispatch,
  },
} = wp;

const CloneReplacePluginInfo = ({
  meta: {
    isCurrentPostPublished,
    getCurrentPostId,
    getCurrentPost,
    replacePostID,
    originalPostID,
  },
  setReplacePostId,
}) => {
  const [isOpen, setOpen] = useState(false);
  const [replacePostTitle, setReplacePostTitle] = useState('');
  const isPostPublished = isCurrentPostPublished();
  const isCloned = (!!originalPostID);

  return (
    <PluginPostStatusInfo>
      <div id="clone-replace-actions">
        {!isPostPublished && (
          <a
            href="#clone-replace"
            role="button"
            onClick={() => setOpen(true)}
          >
            {__('Clone/Replace', 'clone-replace')}
          </a>
        )}

        {replacePostTitle && (
          <p style={{ marginTop: '0.33em' }}>
            {sprintf(
              __('Set to replace: %s', 'clone-replace'),
              replacePostTitle,
            )}
          </p>
        )}

        {(isPostPublished || (isCloned && isOpen)) && (
          <CloneButton />
        )}

        {!isPostPublished && isOpen && (
          <>
            <div id="replace-action">
              <h4 style={{ marginBottom: '0.33em' }}>
                {__('Replace', 'clone-replace')}
              </h4>

              <p>
                {__('When this post is published, it will replace the selected post.', 'clone-replace')}
                {__('The data from this post will be moved to the replaced one,', 'clone-replace')}
                {__('the latest version of the replaced post will become a revision if revisions are enabled, ', 'clone-replace')}
                {__('or go to the trash if not, and this post will be deleted. There is no undo, per se.', 'clone-replace')}
              </p>

              {isCloned && (
                <p>
                  <a
                    href="#clone-replace"
                    role="button"
                    onClick={() => {
                      setOpen(false);
                      setReplacePostTitle(getCurrentPost().title);
                      setReplacePostId(originalPostID);
                    }}
                  >
                    {__('Replace original post', 'clone-replace')}
                  </a>
                </p>
              )}

              <PostSelector
                onChange={(post) => {
                  setReplacePostId(post.id);
                  setReplacePostTitle(post.title);
                }}
                threshold={2}
                currentPostID={replacePostID ?? getCurrentPostId()}
                label={__('Find a post to replace', 'clone-replace')}
              />
            </div>

            <p style={{ marginTop: '1.2em' }}>
              <button
                style={{ marginRight: '0.5em' }}
                type="button"
                className="button"
                onClick={() => setOpen(false)}
              >
                {__('OK', 'clone-replace')}
              </button>
              <a
                href="#clone-replace"
                role="button"
                onClick={() => {
                  setOpen(false);
                  setReplacePostId(0);
                  setReplacePostTitle('');
                }}
              >
                {__('Cancel', 'clone-replace')}
              </a>
            </p>
          </>
        )}
      </div>
    </PluginPostStatusInfo>
  );
};

CloneReplacePluginInfo.propTypes = {
  meta: PropTypes.shape({
    replacePostID: PropTypes.number,
    originalPostID: PropTypes.number,
    isCurrentPostPublished: PropTypes.bool,
    getCurrentPostId: PropTypes.number,
    getCurrentPost: PropTypes.func,
  }).isRequired,
  setReplacePostId: PropTypes.func.isRequired,
};

export default compose([
  withSelect((select) => {
    const editor = select('core/editor');

    const {
      isCurrentPostPublished,
      getCurrentPostId,
      getCurrentPost,
      getEditedPostAttribute,
    } = editor;

    const {
      _cr_original_post: originalPostID = 0,
      _cr_replace_post_id: replacePostID = 0,
    } = getEditedPostAttribute('meta');

    return {
      meta: {
        isCurrentPostPublished,
        getCurrentPostId,
        getCurrentPost,
        replacePostID,
        originalPostID,
      },
      post: getCurrentPost(),
    };
  }),
  withDispatch((dispatch) => ({
    setReplacePostId: (metaValue) => {
      dispatch('core/editor').editPost({
        meta: {
          _cr_replace_post_id: metaValue,
        },
      });
    },
  })),
])(CloneReplacePluginInfo);
