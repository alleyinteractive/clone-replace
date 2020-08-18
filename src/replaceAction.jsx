import React from 'react';
import PostSelector from './postSelector';

const { __ } = wp.i18n;

const HeaderStyle = {
  marginBottom: '0.33em',
};

const ReplaceAction = () => (
  <div id="replace-action">
    <h4 style={HeaderStyle}>
      {__('Replace', 'clone-replace')}
    </h4>

    <div className="cr-notice">
      <p>
        {__('When this post is published, it will replace the selected post.', 'clone-replace')}
        {__('The data from this post will be moved to the replaced one,.', 'clone-replace')}
        {__('the latest version of the replaced post will become a revision if revisions are enabled,', 'clone-replace')}
        {__('or go to the trash if not, and this post will be deleted. There is no undo, per se.', 'clone-replace')}
      </p>
    </div>

    <p>
      <a
        href="#section"
        onClick={() => {
          console.log('Click Test');
        }}
      >
        {__('Replace original post', 'clone-replace')}
      </a>
    </p>

    <p>
      {__('Find a post to replace', 'clone-replace')}
    </p>

    <PostSelector
      onChange={() => {
        console.log('Chaning post selector.');
      }}
      postTypes={['post']}
      threshold={2}
      label={__('Select post', 'clone-replace')}
    />
  </div>
);

export default ReplaceAction;
