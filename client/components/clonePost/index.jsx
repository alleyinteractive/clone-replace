/* eslint-disable no-underscore-dangle */
/* global React, cloneReplace */
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Slotfill to add clone & replace controls to the sidebar.
 */
const ClonePost = () => {
  const currentPost = useSelect((select) => select('core/editor').getCurrentPost(), []);

  return (
    <div>
      <a href={`${cloneReplace.adminUrl}admin-post.php?action=clone_post&p=${currentPost.id}&_wpnonce=${cloneReplace.nonce}`}>
        {__('Clone Post', 'clone-replace')}
      </a>
    </div>
  );
};

export default ClonePost;
