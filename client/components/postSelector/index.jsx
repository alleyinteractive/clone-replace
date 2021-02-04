// Dependencies.
import React, { useEffect, useRef, useState } from 'react';
import PropTypes from 'prop-types';
import apiFetch from '@wordpress/api-fetch';
import classNames from 'classnames';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { v4 as uuidv4 } from 'uuid';

// Components.
import SearchResults from './components/searchResults';

// Custom hooks.
import useDebounce from './hooks/useDebounce';

// Styles.
import './styles.scss';

/**
 * Render autocomplete component.
 */
const PostSelector = ({
  className,
  emptyLabel,
  label,
  maxPages,
  multiple,
  onSelect,
  placeHolder,
  postTypes,
  selected,
  threshold,
}) => {
  // Unique ID.
  const uniqueKey = uuidv4();

  // Setup state.
  const [error, setError] = useState('');
  const [foundPosts, setFoundPosts] = useState([]);
  const [isOpen, setIsOpen] = useState(false);
  const [loading, setLoadState] = useState(false);
  const [searchString, setSearchString] = useState('');
  const [selectedPosts, setSelectedPosts] = useState([]);

  // Create ref.
  const ref = useRef();

  // Debounce search string from input.
  const debouncedSearchString = useDebounce(searchString, 750);

  /**
   * Make API request for posts by search string.
   *
   * @param {int} page current page number.
   */
  const fetchPosts = async (page = 1) => {
    // Prevent fetch if we haven't
    // met our search string threshold.
    if (debouncedSearchString.length < threshold) {
      setFoundPosts([]);
      return;
    }

    // Page count.
    let totalPages = 0;

    if (page === 1) {
      // Reset state before we start the fetch.
      setFoundPosts([]);

      // Set the loading flag.
      setLoadState(true);
    }

    // Get search results from the API and store them.
    const path = addQueryArgs(
      '/wp/v2/search',
      {
        page,
        search: debouncedSearchString,
        subtype: postTypes.length > 0 ? postTypes.join(',') : 'any',
        type: 'post',
      },
    );

    // Fetch posts by page.
    await apiFetch({ path, parse: false })
      .then((response) => {
        const totalPagesFromResponse = parseInt(
          response.headers.get('X-WP-TotalPages'),
          10,
        );
        // Set totalPage count to received page count unless larger than maxPages prop.
        totalPages = totalPagesFromResponse > maxPages
          ? maxPages : totalPagesFromResponse;
        return response.json();
      })
      .then((posts) => {
        setFoundPosts((prevState) => [...prevState, ...posts]);
        setLoadState(false);

        // Continue to fetch additional page results.
        if (
          (totalPages && totalPages > page)
          || (page >= 1 && multiple && selectedPosts.length > 0)
        ) {
          fetchPosts(page + 1);
        }
      })
      .catch((err) => setError(err.message));
  };

  /**
   * On Mount, pre-fill selected buttons, if they exist.
   */
  useEffect(() => {
    setSelectedPosts(selected);
  }, []);

  /**
   * Handles submitting the input value on debounce.
   */
  useEffect(() => {
    if (debouncedSearchString && threshold <= debouncedSearchString.length) {
      fetchPosts();
    } else { setFoundPosts([]); }
  }, [debouncedSearchString, threshold]);

  /**
   * Mousedown event callback.
   *
   * @param {MouseEvent} event mouse event.
   */
  const handleClick = (event) => {
    setIsOpen(ref && ref.current.contains(event.target));
  };

  /**
   * Keydown event callback.
   *
   * @param {KeyboardEvent} event keyboard event.
   */
  const handleKeyboard = (event) => {
    if (event.keyCode === 27) { setIsOpen(false); }
  };

  /**
   * Handle keydown.
   */
  useEffect(() => {
    document.addEventListener('keydown', handleKeyboard);
    return () => document.removeEventListener('keydown', handleKeyboard);
  });

  /**
   * Handles mouse down.
   */
  useEffect(() => {
    document.addEventListener('mousedown', handleClick);
    return () => document.removeEventListener('mousedown', handleClick);
  });

  /**
   * Handle post selection from search results
   * and return value to parent.
   *
   * @param {object} post selected post object.
   */
  const handlePostSelection = (post) => {
    let newSelectedPosts = [];

    // If multiple post selection is available.
    // Add selection to foundPosts array.
    if (selectedPosts.some((item) => item.id === post.id)) {
      const index = selectedPosts.findIndex((item) => item.id === post.id);
      newSelectedPosts = [
        ...selectedPosts.slice(0, index),
        ...selectedPosts.slice(index + 1, selectedPosts.length),
      ];
    } else if (multiple) {
      newSelectedPosts = [
        ...selectedPosts,
        post,
      ];
    } else {
      // Set single post to state.
      newSelectedPosts = [post];
      // Reset state and close dropdown.
      setIsOpen(false);
    }

    setSelectedPosts(newSelectedPosts);
    onSelect(newSelectedPosts);
  };

  return (
    <form
      className="autocomplete__component"
      onSubmit={(event) => event.preventDefault()}
    >
      <div
        className={
          classNames(
            'components-base-control',
            'autocomplete-base-control',
            className,
          )
        }
        ref={ref}
      >
        <div
          aria-expanded={isOpen}
          aria-haspopup="listbox"
          aria-owns={`listbox-${uniqueKey}`}
          className={
            classNames(
              'components-base-control__field',
              'autocomplete-base-control__field',
            )
          }
          role="combobox" // eslint-disable-line jsx-a11y/role-has-required-aria-props
        >
          <label
            className={
              classNames(
                'components-base-control__label',
                'autocomplete-base-control__label',
              )
            }
            htmlFor={`autocomplete-${uniqueKey}`}
          >
            <div>{label}</div>
          </label>
          {selectedPosts.length > 0 && (
            <ul
              role="listbox"
              aria-labelledby={`autocomplete-${uniqueKey}`}
              id={`selected-posts-${uniqueKey}`}
              className={
                classNames(
                  'autocomplete__selection--results',
                  'autocomplete__selection-list',
                )
              }
            >
              {selectedPosts.map((item) => (
                <li
                  className="autocomplete__selection-list--item"
                  key={item.title}
                >
                  <Button
                    className="autocomplete__selection-list--item--button"
                    isSecondary
                    isSmall
                    onClick={() => handlePostSelection(item)}
                    type="button"
                  >
                    {item.title}
                  </Button>
                </li>
              ))}
            </ul>
          )}
          <input
            aria-autocomplete="list"
            autoComplete="off"
            className={
              classNames(
                'components-text-control__input',
                'autocomplete-text-control__input',
                {
                  'autocomplete-text-control__input--working': isOpen,
                },
              )
            }
            id={`autocomplete-${uniqueKey}`}
            onChange={(e) => setSearchString(e.target.value)}
            onFocus={() => setIsOpen(true)}
            placeholder={placeHolder}
            type="text"
            value={searchString}
          />
        </div>
        <SearchResults
          emptyLabel={emptyLabel}
          error={error}
          labelledById={`autocomplete-${uniqueKey}`}
          id={`listbox-${uniqueKey}`}
          isOpen={isOpen}
          loading={loading && debouncedSearchString}
          onSelect={handlePostSelection}
          options={foundPosts}
          selectedPosts={selectedPosts}
          threshold={threshold}
          value={debouncedSearchString}
        />
      </div>
    </form>
  );
};

/**
 * Set initial props.
 * @type {object}
 */
PostSelector.defaultProps = {
  className: '',
  emptyLabel: __('No posts found', 'wp-starter-plugin'),
  label: __('Search for posts', 'wp-starter-plugin'),
  maxPages: 5,
  multiple: false,
  placeHolder: __('Search for posts', 'wp-starter-plugin'),
  postTypes: [],
  selected: [],
  threshold: 3,
};

/**
 * Set PropTypes for this component.
 * @type {object}
 */
PostSelector.propTypes = {
  className: PropTypes.string,
  emptyLabel: PropTypes.string,
  label: PropTypes.string,
  maxPages: PropTypes.number,
  multiple: PropTypes.bool,
  onSelect: PropTypes.func.isRequired,
  placeHolder: PropTypes.string,
  postTypes: PropTypes.arrayOf(PropTypes.string),
  selected: PropTypes.arrayOf([
    PropTypes.shape({
      id: PropTypes.number,
      title: PropTypes.string,
    }),
  ]),
  threshold: PropTypes.number,
};

export default PostSelector;
