/* eslint-disable no-underscore-dangle */
/* global React */

import apiFetch from '@wordpress/api-fetch';
import { Spinner } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import PostSelector from '../postSelector';

/**
 * Slotfill to add clone & replace controls to the sidebar.
 */
const ReplacePost = () => {
  const { editPost } = useDispatch('core/editor');
  const {
    meta,
    postType,
    currentPost,
  } = useSelect((select) => ({
    meta: select('core/editor').getEditedPostAttribute('meta') || {},
    postType: select('core/editor').getCurrentPostType(),
    currentPost: select('core/editor').getCurrentPost(),
  }), []);

  const [replacePostId, setReplacePostId] = useState(meta._cr_replace_post_id);
  const [replacePost, setReplacePost] = useState(false);
  const selected = replacePost ? [replacePost] : [];

  const fetchPost = async (postId) => {
    const post = await apiFetch({ path: `/wp/v2/${postType}/${postId}` });
    setReplacePost({
      id: post.id,
      title: post.title.rendered,
    });
  };

  useEffect(() => {
    editPost({
      meta: {
        ...meta,
        _cr_replace_post_id: replacePostId.toString(),
      },
    });
  }, [replacePostId]);

  useEffect(() => {
    if (replacePostId) {
      fetchPost(replacePostId);
    }
  }, []);

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
        onSelect={(val) => {
          setReplacePost(val.length ? val[0] : false);
          setReplacePostId(val.length ? val[0].id : '');
        }}
        postTypes={[postType]}
        selected={selected}
        endpoint="/clone-replace/v1/search"
      />
    </div>
  );
};

export default ReplacePost;
