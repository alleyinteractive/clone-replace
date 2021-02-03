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
/******/ 	return __webpack_require__(__webpack_require__.s = "./client/index.js");
/******/ })
/************************************************************************/
/******/ ({

/***/ "./client/index.js":
/*!*************************!*\
  !*** ./client/index.js ***!
  \*************************/
/*! no static exports found */
/***/ (function(module, exports, __webpack_require__) {

"use strict";
eval("\n\n/* global wp, React */\n\nvar _wp = wp,\n    registerPlugin = _wp.plugins.registerPlugin,\n    PluginPostStatusInfo = _wp.editPost.PluginPostStatusInfo;\n\n\nvar CloneReplaceStatusInfo = function CloneReplaceStatusInfo() {\n  return React.createElement(\n    PluginPostStatusInfo,\n    null,\n    React.createElement(\n      'div',\n      null,\n      'Post stutus info'\n    )\n  );\n};\n\nregisterPlugin('clone-replace-pre-publish-panel-test', {\n  render: CloneReplaceStatusInfo\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9jbGllbnQvaW5kZXguanMuanMiLCJzb3VyY2VzIjpbIndlYnBhY2s6Ly8vY2xpZW50L2luZGV4LmpzPzRlODgiXSwic291cmNlc0NvbnRlbnQiOlsiLyogZ2xvYmFsIHdwLCBSZWFjdCAqL1xuXG5jb25zdCB7XG4gIHBsdWdpbnM6IHtcbiAgICByZWdpc3RlclBsdWdpbixcbiAgfSxcbiAgZWRpdFBvc3Q6IHtcbiAgICBQbHVnaW5Qb3N0U3RhdHVzSW5mbyxcbiAgfSxcbn0gPSB3cDtcblxuY29uc3QgQ2xvbmVSZXBsYWNlU3RhdHVzSW5mbyA9ICgpID0+IChcbiAgPFBsdWdpblBvc3RTdGF0dXNJbmZvPlxuICAgIDxkaXY+UG9zdCBzdHV0dXMgaW5mbzwvZGl2PlxuICA8L1BsdWdpblBvc3RTdGF0dXNJbmZvPlxuKTtcblxucmVnaXN0ZXJQbHVnaW4oJ2Nsb25lLXJlcGxhY2UtcHJlLXB1Ymxpc2gtcGFuZWwtdGVzdCcsIHtcbiAgcmVuZGVyOiBDbG9uZVJlcGxhY2VTdGF0dXNJbmZvLFxufSk7XG5cbiJdLCJtYXBwaW5ncyI6Ijs7QUFBQTtBQUNBO0FBUUE7QUFMQTtBQUdBO0FBQ0E7QUFDQTtBQUVBO0FBQUE7QUFDQTtBQUFBO0FBQ0E7QUFBQTtBQUFBO0FBQUE7QUFBQTtBQURBO0FBREE7QUFDQTtBQUtBO0FBQ0E7QUFEQSIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./client/index.js\n");

/***/ })

/******/ });