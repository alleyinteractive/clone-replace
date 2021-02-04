/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/index.jsx");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/index.jsx":
/*!**************************!*\
  !*** ./client/index.jsx ***!
  \**************************/
/*! no static exports found */
/***/ (function(module, exports) {

eval("function ownKeys(object, enumerableOnly) { var keys = Object.keys(object); if (Object.getOwnPropertySymbols) { var symbols = Object.getOwnPropertySymbols(object); if (enumerableOnly) symbols = symbols.filter(function (sym) { return Object.getOwnPropertyDescriptor(object, sym).enumerable; }); keys.push.apply(keys, symbols); } return keys; }\n\nfunction _objectSpread(target) { for (var i = 1; i < arguments.length; i++) { var source = arguments[i] != null ? arguments[i] : {}; if (i % 2) { ownKeys(Object(source), true).forEach(function (key) { _defineProperty(target, key, source[key]); }); } else if (Object.getOwnPropertyDescriptors) { Object.defineProperties(target, Object.getOwnPropertyDescriptors(source)); } else { ownKeys(Object(source)).forEach(function (key) { Object.defineProperty(target, key, Object.getOwnPropertyDescriptor(source, key)); }); } } return target; }\n\nfunction _defineProperty(obj, key, value) { if (key in obj) { Object.defineProperty(obj, key, { value: value, enumerable: true, configurable: true, writable: true }); } else { obj[key] = value; } return obj; }\n\nfunction _slicedToArray(arr, i) { return _arrayWithHoles(arr) || _iterableToArrayLimit(arr, i) || _unsupportedIterableToArray(arr, i) || _nonIterableRest(); }\n\nfunction _nonIterableRest() { throw new TypeError(\"Invalid attempt to destructure non-iterable instance.\\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method.\"); }\n\nfunction _unsupportedIterableToArray(o, minLen) { if (!o) return; if (typeof o === \"string\") return _arrayLikeToArray(o, minLen); var n = Object.prototype.toString.call(o).slice(8, -1); if (n === \"Object\" && o.constructor) n = o.constructor.name; if (n === \"Map\" || n === \"Set\") return Array.from(o); if (n === \"Arguments\" || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(n)) return _arrayLikeToArray(o, minLen); }\n\nfunction _arrayLikeToArray(arr, len) { if (len == null || len > arr.length) len = arr.length; for (var i = 0, arr2 = new Array(len); i < len; i++) { arr2[i] = arr[i]; } return arr2; }\n\nfunction _iterableToArrayLimit(arr, i) { if (typeof Symbol === \"undefined\" || !(Symbol.iterator in Object(arr))) return; var _arr = []; var _n = true; var _d = false; var _e = undefined; try { for (var _i = arr[Symbol.iterator](), _s; !(_n = (_s = _i.next()).done); _n = true) { _arr.push(_s.value); if (i && _arr.length === i) break; } } catch (err) { _d = true; _e = err; } finally { try { if (!_n && _i[\"return\"] != null) _i[\"return\"](); } finally { if (_d) throw _e; } } return _arr; }\n\nfunction _arrayWithHoles(arr) { if (Array.isArray(arr)) return arr; }\n\n/* eslint-disable no-underscore-dangle */\n\n/* global React, crNonce */\nvar _wp = wp,\n    TextControl = _wp.components.TextControl,\n    PluginPostStatusInfo = _wp.editPost.PluginPostStatusInfo,\n    registerPlugin = _wp.plugins.registerPlugin,\n    select = _wp.data.select,\n    useState = _wp.element.useState;\n\nvar CloneReplaceStatusInfo = function CloneReplaceStatusInfo() {\n  var meta = select('core/editor').getEditedPostAttribute('meta') || {};\n\n  var _useState = useState({\n    originalPost: meta._cr_original,\n    replacePostId: meta._cr_replace_post_id,\n    replacingPostId: meta._cr_replace_post_id\n  }),\n      _useState2 = _slicedToArray(_useState, 2),\n      inputs = _useState2[0],\n      setInputs = _useState2[1];\n\n  var handleChange = function handleChange(val) {\n    setInputs(_objectSpread(_objectSpread({}, inputs), {}, {\n      replacePostId: val\n    }));\n  };\n\n  return /*#__PURE__*/React.createElement(PluginPostStatusInfo, null, /*#__PURE__*/React.createElement(\"div\", null, /*#__PURE__*/React.createElement(TextControl, {\n    onChange: handleChange,\n    label: \"Find a post to replace\"\n  }), /*#__PURE__*/React.createElement(TextControl, {\n    label: \"cr_replace_post_id\",\n    name: \"cr_replace_post_id\",\n    value: inputs.replacePostId\n  }), /*#__PURE__*/React.createElement(TextControl, {\n    label: \"replace_with\",\n    name: \"replace_with_\".concat(inputs.replacePostId),\n    value: crNonce\n  })));\n};\n\nregisterPlugin('clone-replace-pre-publish-panel-test', {\n  render: CloneReplaceStatusInfo\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9jbGllbnQvaW5kZXguanN4LmpzIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vLy4vY2xpZW50L2luZGV4LmpzeD8xYzA4Il0sInNvdXJjZXNDb250ZW50IjpbIi8qIGVzbGludC1kaXNhYmxlIG5vLXVuZGVyc2NvcmUtZGFuZ2xlICovXG4vKiBnbG9iYWwgUmVhY3QsIGNyTm9uY2UgKi9cblxuY29uc3Qge1xuICBjb21wb25lbnRzOiB7XG4gICAgVGV4dENvbnRyb2wsXG4gIH0sXG4gIGVkaXRQb3N0OiB7XG4gICAgUGx1Z2luUG9zdFN0YXR1c0luZm8sXG4gIH0sXG4gIHBsdWdpbnM6IHtcbiAgICByZWdpc3RlclBsdWdpbixcbiAgfSxcbiAgZGF0YToge1xuICAgIHNlbGVjdCxcbiAgfSxcbiAgZWxlbWVudDoge1xuICAgIHVzZVN0YXRlLFxuICAgIC8vIHVzZUVmZmVjdCxcbiAgfSxcbn0gPSB3cDtcblxuY29uc3QgQ2xvbmVSZXBsYWNlU3RhdHVzSW5mbyA9ICgpID0+IHtcbiAgY29uc3QgbWV0YSA9IHNlbGVjdCgnY29yZS9lZGl0b3InKS5nZXRFZGl0ZWRQb3N0QXR0cmlidXRlKCdtZXRhJykgfHwge307XG4gIGNvbnN0IFtpbnB1dHMsIHNldElucHV0c10gPSB1c2VTdGF0ZSh7XG4gICAgb3JpZ2luYWxQb3N0OiBtZXRhLl9jcl9vcmlnaW5hbCxcbiAgICByZXBsYWNlUG9zdElkOiBtZXRhLl9jcl9yZXBsYWNlX3Bvc3RfaWQsXG4gICAgcmVwbGFjaW5nUG9zdElkOiBtZXRhLl9jcl9yZXBsYWNlX3Bvc3RfaWQsXG4gIH0pO1xuXG4gIGNvbnN0IGhhbmRsZUNoYW5nZSA9ICh2YWwpID0+IHtcbiAgICBzZXRJbnB1dHMoe1xuICAgICAgLi4uaW5wdXRzLFxuICAgICAgcmVwbGFjZVBvc3RJZDogdmFsLFxuICAgIH0pO1xuICB9O1xuXG4gIHJldHVybiAoXG4gICAgPFBsdWdpblBvc3RTdGF0dXNJbmZvPlxuICAgICAgPGRpdj5cbiAgICAgICAgPFRleHRDb250cm9sIG9uQ2hhbmdlPXtoYW5kbGVDaGFuZ2V9IGxhYmVsPVwiRmluZCBhIHBvc3QgdG8gcmVwbGFjZVwiIC8+XG4gICAgICAgIDxUZXh0Q29udHJvbFxuICAgICAgICAgIGxhYmVsPVwiY3JfcmVwbGFjZV9wb3N0X2lkXCJcbiAgICAgICAgICBuYW1lPVwiY3JfcmVwbGFjZV9wb3N0X2lkXCJcbiAgICAgICAgICB2YWx1ZT17aW5wdXRzLnJlcGxhY2VQb3N0SWR9XG4gICAgICAgIC8+XG4gICAgICAgIDxUZXh0Q29udHJvbFxuICAgICAgICAgIGxhYmVsPVwicmVwbGFjZV93aXRoXCJcbiAgICAgICAgICBuYW1lPXtgcmVwbGFjZV93aXRoXyR7aW5wdXRzLnJlcGxhY2VQb3N0SWR9YH1cbiAgICAgICAgICB2YWx1ZT17Y3JOb25jZX1cbiAgICAgICAgLz5cbiAgICAgIDwvZGl2PlxuICAgIDwvUGx1Z2luUG9zdFN0YXR1c0luZm8+XG4gICk7XG59O1xuXG5yZWdpc3RlclBsdWdpbignY2xvbmUtcmVwbGFjZS1wcmUtcHVibGlzaC1wYW5lbC10ZXN0Jywge1xuICByZW5kZXI6IENsb25lUmVwbGFjZVN0YXR1c0luZm8sXG59KTtcblxuIl0sIm1hcHBpbmdzIjoiOzs7Ozs7Ozs7Ozs7Ozs7Ozs7QUFBQTtBQUNBO0FBQUE7QUFtQkE7QUFmQTtBQUdBO0FBR0E7QUFHQTtBQUdBO0FBQ0E7QUFJQTtBQUNBO0FBQ0E7QUFGQTtBQUdBO0FBQ0E7QUFDQTtBQUhBO0FBRkE7QUFBQTtBQUFBO0FBQ0E7QUFPQTtBQUNBO0FBRUE7QUFGQTtBQUlBO0FBQ0E7QUFDQTtBQUdBO0FBQUE7QUFBQTtBQUVBO0FBQ0E7QUFDQTtBQUhBO0FBTUE7QUFDQTtBQUNBO0FBSEE7QUFRQTtBQUNBO0FBQ0E7QUFDQTtBQURBIiwic291cmNlUm9vdCI6IiJ9\n//# sourceURL=webpack-internal:///./client/index.jsx\n");

/***/ })

/******/ });