/* eslint-disable no-underscore-dangle */
/* global React, cloneReplace */
import { select } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Slotfill to add clone & replace controls to the sidebar.
 */
const ClonePost = () => {
  const currentPost = select('core/editor').getCurrentPost();

  /**
   * We only are interested in draft posts here.
   */
  if (currentPost.status === 'draft') {
    return null;
  }

  return (
    <div>
      <a href={`${cloneReplace.homeUrl}/wp-admin/admin-post.php?action=clone_post&p=${currentPost.id}&_wpnonce=${cloneReplace.crNonce}`}>
        {__('Clone Post', 'clone-replace')}
      </a>
    </div>
  );
};

export default ClonePost;
