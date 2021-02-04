/* eslint-disable no-underscore-dangle */
/* global React, crNonce */

const {
  components: {
    TextControl,
  },
  editPost: {
    PluginPostStatusInfo,
  },
  plugins: {
    registerPlugin,
  },
  data: {
    select,
  },
  element: {
    useState,
    // useEffect,
  },
} = wp;

const CloneReplaceStatusInfo = () => {
  const meta = select('core/editor').getEditedPostAttribute('meta') || {};
  const [inputs, setInputs] = useState({
    originalPost: meta._cr_original,
    replacePostId: meta._cr_replace_post_id,
    replacingPostId: meta._cr_replace_post_id,
  });

  const handleChange = (val) => {
    setInputs({
      ...inputs,
      replacePostId: val,
    });
  };

  return (
    <PluginPostStatusInfo>
      <div>
        <TextControl onChange={handleChange} label="Find a post to replace" />
        <TextControl
          label="cr_replace_post_id"
          name="cr_replace_post_id"
          value={inputs.replacePostId}
        />
        <TextControl
          label="replace_with"
          name={`replace_with_${inputs.replacePostId}`}
          value={crNonce}
        />
      </div>
    </PluginPostStatusInfo>
  );
};

registerPlugin('clone-replace-pre-publish-panel-test', {
  render: CloneReplaceStatusInfo,
});

