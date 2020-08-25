import React from 'react';
import PropTypes from 'prop-types';
import debounce from 'lodash/debounce';

const {
  apiFetch,
  components: {
    SelectControl,
    TextControl,
  },
  i18n: {
    __,
  },
  url: {
    addQueryArgs,
  },
} = wp;

/**
 * A React component that allows users to select posts by fuzzy search.
 *
 * This component takes three props:
 *
 * onChange: Required. A function that accepts a post object when selected.
 * postTypes: Optional. An array of post type slugs available for search.
 *   Default is to search all post types.
 * threshold: Optional. Minimum number of characters entered to initialize search. Defaults to 3.
 *
 * This component *does not* keep track of the selected post. It is up to the
 * parent component to keep track of the selected post and show the selected
 * status. This component is intended to be used when selecting a post for the
 * first time, or selecting a new post.
 */
export default class PostSelector extends React.PureComponent {
  /**
   * Constructor. Binds function scope.
   * @param {object} props - Props for this component.
   */
  constructor(props) {
    super(props);
    // Define initial state for this component.
    this.state = {
      foundPosts: [],
      loading: false,
      searchText: '',
    };
    this.handlePostSelect = this.handlePostSelect.bind(this);
    this.handleSearchTextChange = this.handleSearchTextChange.bind(this);
    this.handleSearchTextSubmit = debounce(
      this.handleSearchTextSubmit.bind(this), 1500,
    );
  }

  /**
   * Handles a post selection. Matches the post ID to the list of found posts,
   * extracts the relevant post object, and calls `onChange` from props with
   * the found post object.
   * @param {string} postId - The selected post ID.
   */
  handlePostSelect(postId) {
    const {
      onChange,
    } = this.props;
    const {
      foundPosts,
    } = this.state;

    // Attempt to find post by ID.
    const postIdNumber = parseInt(postId, 10);
    const foundPost = foundPosts.find((post) => postIdNumber === post.id);
    if (!foundPost) {
      return;
    }

    // Call the passed onChange function from props with the post object.
    onChange(foundPost);
  }

  /**
   * Handles a change to the search text string.
   * @param {string} searchText - The new search text to apply.
   */
  handleSearchTextChange(searchText) {
    this.setState({
      searchText,
    });

    this.handleSearchTextSubmit();
  }

  /**
   * Handles submitting the input value.
   */
  handleSearchTextSubmit() {
    const {
      searchText,
    } = this.state;

    this.loadFoundPosts(searchText);
  }

  /**
   * Loads found posts for the given post type and search text from the API.
   * @param {string} searchText - The text string to use when searching.
   */
  loadFoundPosts(searchText) {
    const {
      postTypes,
      currentPostID,
      threshold,
    } = this.props;

    // If the search text is not at the threshold, bail.
    if (threshold > searchText.length) {
      return;
    }

    // Set the loading flag.
    this.setState({ loading: true });

    // Get search results from the API and store them.
    const path = addQueryArgs(
      '/wp/v2/search',
      {
        search: searchText,
        subtype: postTypes ? postTypes.join() : 'any',
        type: 'clone_replace',
        current_post_id: currentPostID,
      },
    );
    apiFetch({ path })
      .then((foundPosts) => this.setState({
        foundPosts,
        loading: false,
      }));
  }

  /**
   * Renders component markup.
   * @returns {object} - JSX for this component.
   */
  render() {
    const {
      label,
    } = this.props;

    const {
      foundPosts = [],
      loading = false,
      searchText = '',
    } = this.state;

    return (
      <form
        // Prevent accidental page reload/redirects.
        onSubmit={(event) => event.preventDefault()}
      >
        <TextControl
          label={label}
          onChange={this.handleSearchTextChange}
          value={searchText}
        />
        {loading === true && (
          <div>{__('Loading...', 'clone-replace')}</div>
        )}
        {loading === false && searchText !== '' && foundPosts.length === 0 && (
          <div>{__('No matching posts found.', 'clone-replace')}</div>
        )}
        {foundPosts.length > 0 && (
          <SelectControl
            label={__('Selected Post', 'clone-replace')}
            onChange={this.handlePostSelect}
            options={[
              {
                label: __('Select post', 'clone-replace'),
                value: '',
              },
              ...foundPosts.map((post) => ({
                label: `${post.subtype.toUpperCase()}: ${post.title} (ID: ${post.id})`, // eslint-disable-line max-len
                value: post.id,
              })),
            ]}
          />
        )}
      </form>
    );
  }
}

/**
 * Default props for this component.
 * @type {object}
 */
PostSelector.defaultProps = {
  postTypes: [],
  currentPostID: 0,
  threshold: 3,
  label: __('Search Text', 'clone-replace'),
};

/**
 * PropTypes for this component.
 * @type {object}
 */
PostSelector.propTypes = {
  onChange: PropTypes.func.isRequired,
  postTypes: PropTypes.arrayOf(PropTypes.string),
  threshold: PropTypes.number,
  currentPostID: PropTypes.number,
  label: PropTypes.string,
};
