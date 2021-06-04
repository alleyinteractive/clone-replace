/* eslint-disable no-underscore-dangle */
/* global React */

import { Spinner } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

// Components.
import PostSelector from '../postSelector';

/**
 * Slotfill to add replace controls to the sidebar.
 */
const ReplacePost = () => {
  const { editPost } = useDispatch('core/editor');

  // Get information about the post.
  const {
    currentPost,
    meta,
    meta: {
      _cr_replace_post_id: replacePostId = '',
    },
    postType,
  } = useSelect((select) => ({
    currentPost: select('core/editor').getCurrentPost(),
    meta: select('core/editor').getEditedPostAttribute('meta') || {},
    postType: select('core/editor').getCurrentPostType(),
  }));

  // Listen for replacements.
  const { cr_replacement_url: replacementUrl } = currentPost || {};
  if (replacementUrl) {
    // Wipe out the contents of the editor and replace them with a notice and a link.
    const link = document.createElement('a');
    link.setAttribute('href', replacementUrl);
    link.innerText = 'Go to the newly replaced post now';
    const message = document.createElement('p');
    message.innerText = 'This post has replaced the original post and no longer exists. You will be redirected there momentarily. ';
    message.appendChild(link);
    const warning = document.createElement('div');
    warning.classList.add('notice', 'notice-warning');
    warning.appendChild(message);
    warning.style.display = 'block';
    const container = document.getElementById('wpbody-content');
    container.innerHTML = '';
    container.appendChild(warning);

    // Now that we've successfully obliterated Gutenberg, redirect.
    setTimeout(() => {
      window.location.href = replacementUrl;
    }, 1000);
  }

  /*
   * Get the replacement post object from the store or the API on load
   * and whenever the replacement post ID changes.
   */
  const {
    replacePost = null,
  } = useSelect((select) => ({
    replacePost: replacePostId
      ? select('core').getEntityRecord(
        'postType',
        postType,
        parseInt(replacePostId, 10),
      ) : null,
  }), [replacePostId]);

  const selected = replacePost
    ? [{
      id: replacePost.id,
      title: replacePost.title.rendered,
    }] : [];

  /**
   * We only are interested in draft posts here.
   */
  if (currentPost.status !== 'draft') {
    return null;
  }

  /**
   * If the post already has a postId saved to meta,
   * show the spinner while we fetch the post object and hydrate the component.
   */
  if (replacePostId && !replacePost) {
    return (
      <Spinner />
    );
  }

  return (
    <div>
      <div>{__('Replace', 'clone-replace')}</div>
      {replacePost ? (
        <strong>
          {__('This post is set to replace: ', 'clone-replace')}
        </strong>
      ) : null}
      <PostSelector
        label={__('', 'clone-replace')}
        placeHolder={__('Search for a post to replace', 'clone-replace')}
        onSelect={(newPost) => editPost({
          meta: {
            ...meta,
            _cr_replace_post_id: newPost && newPost[0] && newPost[0].id
              ? String(newPost[0].id)
              : '',
          },
        })}
        postTypes={[postType]}
        selected={selected}
        endpoint="/clone-replace/v1/search"
      />
    </div>
  );
};

export default ReplacePost;
