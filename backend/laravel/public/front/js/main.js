/******/ (function() { // webpackBootstrap
/******/ 	var __webpack_modules__ = ({

/***/ "./resources/js/front/auth.js":
/*!************************************!*\
  !*** ./resources/js/front/auth.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "ajaxForm": function() { return /* binding */ ajaxForm; },
/* harmony export */   "ajaxRequest": function() { return /* binding */ ajaxRequest; }
/* harmony export */ });
function _typeof(obj) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (obj) { return typeof obj; } : function (obj) { return obj && "function" == typeof Symbol && obj.constructor === Symbol && obj !== Symbol.prototype ? "symbol" : typeof obj; }, _typeof(obj); }

var toast;
$(function () {
  /* Toast from sweetalert */
  toast = Swal.mixin({
    toast: true,
    position: 'top-right',
    showConfirmButton: false,
    timer: 2000
  });
  /* Insert span element to all ajax form submit buttons */

  $('.btn-progress').wrapInner('<span></span>');
  /* Remove disabled attribute from all ajax submit buttons */

  $('.btn-progress:not(.cdisabled)').removeAttr('disabled');
  /* Send CSRF token with each ajax request */

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  }); // Close Alert Error Messages Box

  $('.js-errors-container').on('click', '.alert button', function () {
    $(this).closest('.alert').fadeOut(200);
  }); // Close Alert Error Messages Box

  $('.alert button').on('click', function () {
    $(this).closest('.alert').fadeOut(200);
  });
});
/*********************** Ajax Setup ***************************/

var ajaxForm = function ajaxForm(url, data, ths, method) {
  ths = typeof ths === 'undefined' ? null : ths;
  method = typeof method === 'undefined' ? null : method;

  if (ths) {
    var btn = ths.find('.btn-progress');
    btn.prop('disabled', true);
    showLoader(btn);
    var errorsContainer = $(ths).find('.js-errors-container');

    if (errorsContainer.length) {
      errorsContainer.html("");
    }
  }

  var processData, contentType;

  if (_typeof(data) == 'object') {
    processData = false;
    contentType = false;
  }

  method = method ? method : 'POST';
  return $.ajax({
    type: method,
    url: url,
    data: data,
    processData: processData,
    contentType: contentType
  }).fail(function (jqXHR, ajaxOptions, thrownError) {
    ajaxFailed(jqXHR, null, errorsContainer);
  }).always(function () {
    if (btn) {
      btn.prop('disabled', false);
      btn.removeClass('prg-h').find('.spin-h').remove();
    }
  });
};

function ajaxRequest(route, data, ths, route2, method, stop) {
  route2 = typeof route2 === 'undefined' ? null : route2;
  method = typeof method === 'undefined' ? null : method;
  stop = typeof stop === 'undefined' ? null : stop;

  if (ths) {
    var btn = ths.find('.btn-progress');
    btn.prop('disabled', true);
    showLoader(btn);
    var errorsContainer = $(ths).find('.js-errors-container');

    if (errorsContainer.length) {
      errorsContainer.html("");
    }
  }

  var processData, contentType;

  if (_typeof(data) == 'object') {
    processData = false;
    contentType = false;
  }

  method = method ? method : 'POST';
  $.ajax({
    type: method,
    url: route,
    data: data,
    processData: processData,
    contentType: contentType
  }).done(function (r) {
    ajaxDone(r, route2, btn, stop, errorsContainer);
  }).fail(function (e) {
    ajaxFailed(e, null, errorsContainer);
  }).always(function (r) {
    if (ths) {
      btn.prop('disabled', false);
      btn.removeClass('prg-h').find('.spin-h').remove();
    }
  });
}

function ajaxDone(res, route, btn, stop, errorsContainer) {
  route = typeof route === 'undefined' ? null : route;
  stop = typeof stop === 'undefined' ? null : stop;

  if (res.status) {
    if (res.url) {
      window.location.assign(res.url);
      return;
    } else if (route) {
      window.location.assign(route);
      return;
    } else if (res.msg) {
      btn.prop('disabled', true);
      swalDone(res.msg);
    } else if (stop) {} else {
      var currentUrl = window.location.href;
      currentUrl = currentUrl.replace(/#/g, '');
      window.location.assign(currentUrl);
    } //(swal) ? swalDone(res.msg) : toastDone(res.msg);

  } else if (!res.status && res.msg) {
    swalFailed(res.msg);
  }

  if (res.errors) {
    ajaxFailed(null, res.errors, errorsContainer);
  }
}

function ajaxFailed(e, errors, errorsContainer) {
  e = typeof e === 'undefined' ? null : e;
  errors = typeof errors === 'undefined' ? null : errors;

  if (e && e.status == 403) {
    swalFailed(e.responseJSON.message);
    return;
  }

  if (errors || e.status == 422) {
    var layout = '<div class="alert alert-danger" role="alert">' + '<button type="button">×</button>' + '<strong>' + window.errorHeading + '</strong>' + '<ol>';

    if (errors) {
      $.each(errors, function (i, v) {
        layout += '<li>' + v + '</li>';
      });
    } else {
      $.each(e.responseJSON.errors, function (i, v) {
        $.each(v, function (ii, vv) {
          layout += '<li>' + vv + '</li>';
        });
      });
    }

    layout += '</ol></div>';
    errorsContainer.html(layout);
    toastFailed(window.wentWrongError);

    if ($(window).scrollTop() < errorsContainer.offset().top - $(window).innerHeight() + 60) {
      $('html,body').animate({
        scrollTop: errorsContainer.offset().top - $(window).innerHeight() + 60
      }, 700);
    }
  } else {
    swalFailed(e.responseJSON.message);
  }
} // Add Progress Bar on Button when form submit


function showLoader(ths, color) {
  if (!ths.hasClass('prg-h')) {
    var width = ths.outerWidth(),
        height = ths.outerHeight(),
        color = color ? color : '#ffffff',
        h = height / 2 - 2 + 'px';
    ths.addClass('prg-h').css({
      width: width,
      height: height
    }).append('<div class="spin-h"><div class="nb-spinner prg" style="width: ' + h + '; height: ' + h + ';border-top-color: ' + color + '; border-left-color: ' + color + '"></div></div>');
  }
}
/*********************** Helpers ***********************/


function swalFailed(msg) {
  msg = typeof msg === 'undefined' ? null : msg;
  msg = msg ? msg : window.wentWrongError2;
  Swal.fire('Error!', msg, 'error');
}

function swalDone(msg) {
  msg = typeof msg === 'undefined' ? null : msg;
  msg = msg ? msg : window.successfullyDone;
  Swal.fire('Success!', msg, 'success');
}

function toastDone(msg) {
  toast.fire({
    icon: 'success',
    title: msg
  });
}

function toastFailed(msg) {
  toast.fire({
    icon: 'error',
    title: msg
  });
}



/***/ }),

/***/ "./resources/js/front/functions.js":
/*!*****************************************!*\
  !*** ./resources/js/front/functions.js ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _auth__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./auth */ "./resources/js/front/auth.js");

$(function () {
  // Authentication
  $('.register-form').on('submit', function () {
    (0,_auth__WEBPACK_IMPORTED_MODULE_0__.ajaxRequest)(route('register'), $(this).serialize(), $(this));
  });
  $('.login-form').on('submit', function () {
    (0,_auth__WEBPACK_IMPORTED_MODULE_0__.ajaxRequest)(route('login'), $(this).serialize(), $(this));
  });
  $('.forgot-pass-form').on('submit', function () {
    (0,_auth__WEBPACK_IMPORTED_MODULE_0__.ajaxRequest)(route('password.email'), $(this).serialize(), $(this));
  });
  $('.set-newpass-form').on('submit', function () {
    (0,_auth__WEBPACK_IMPORTED_MODULE_0__.ajaxRequest)(route('password.update'), $(this).serialize(), $(this));
  });
});

/***/ }),

/***/ "./resources/js/front/main.js":
/*!************************************!*\
  !*** ./resources/js/front/main.js ***!
  \************************************/
/***/ (function(__unused_webpack_module, __unused_webpack_exports, __webpack_require__) {

$(function () {});

__webpack_require__(/*! ./functions */ "./resources/js/front/functions.js");

/***/ }),

/***/ "./resources/css/front/styles.scss":
/*!*****************************************!*\
  !*** ./resources/css/front/styles.scss ***!
  \*****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/css/admin/admin.scss":
/*!****************************************!*\
  !*** ./resources/css/admin/admin.scss ***!
  \****************************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }),

/***/ "./resources/css/app.css":
/*!*******************************!*\
  !*** ./resources/css/app.css ***!
  \*******************************/
/***/ (function(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

"use strict";
__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = __webpack_modules__;
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/chunk loaded */
/******/ 	!function() {
/******/ 		var deferred = [];
/******/ 		__webpack_require__.O = function(result, chunkIds, fn, priority) {
/******/ 			if(chunkIds) {
/******/ 				priority = priority || 0;
/******/ 				for(var i = deferred.length; i > 0 && deferred[i - 1][2] > priority; i--) deferred[i] = deferred[i - 1];
/******/ 				deferred[i] = [chunkIds, fn, priority];
/******/ 				return;
/******/ 			}
/******/ 			var notFulfilled = Infinity;
/******/ 			for (var i = 0; i < deferred.length; i++) {
/******/ 				var chunkIds = deferred[i][0];
/******/ 				var fn = deferred[i][1];
/******/ 				var priority = deferred[i][2];
/******/ 				var fulfilled = true;
/******/ 				for (var j = 0; j < chunkIds.length; j++) {
/******/ 					if ((priority & 1 === 0 || notFulfilled >= priority) && Object.keys(__webpack_require__.O).every(function(key) { return __webpack_require__.O[key](chunkIds[j]); })) {
/******/ 						chunkIds.splice(j--, 1);
/******/ 					} else {
/******/ 						fulfilled = false;
/******/ 						if(priority < notFulfilled) notFulfilled = priority;
/******/ 					}
/******/ 				}
/******/ 				if(fulfilled) {
/******/ 					deferred.splice(i--, 1)
/******/ 					var r = fn();
/******/ 					if (r !== undefined) result = r;
/******/ 				}
/******/ 			}
/******/ 			return result;
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	!function() {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = function(exports, definition) {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	!function() {
/******/ 		__webpack_require__.o = function(obj, prop) { return Object.prototype.hasOwnProperty.call(obj, prop); }
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	!function() {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = function(exports) {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	}();
/******/ 	
/******/ 	/* webpack/runtime/jsonp chunk loading */
/******/ 	!function() {
/******/ 		// no baseURI
/******/ 		
/******/ 		// object to store loaded and loading chunks
/******/ 		// undefined = chunk not loaded, null = chunk preloaded/prefetched
/******/ 		// [resolve, reject, Promise] = chunk loading, 0 = chunk loaded
/******/ 		var installedChunks = {
/******/ 			"/front/js/main": 0,
/******/ 			"css/app": 0,
/******/ 			"admin_assets/dist/css/admin": 0,
/******/ 			"front/css/styles": 0
/******/ 		};
/******/ 		
/******/ 		// no chunk on demand loading
/******/ 		
/******/ 		// no prefetching
/******/ 		
/******/ 		// no preloaded
/******/ 		
/******/ 		// no HMR
/******/ 		
/******/ 		// no HMR manifest
/******/ 		
/******/ 		__webpack_require__.O.j = function(chunkId) { return installedChunks[chunkId] === 0; };
/******/ 		
/******/ 		// install a JSONP callback for chunk loading
/******/ 		var webpackJsonpCallback = function(parentChunkLoadingFunction, data) {
/******/ 			var chunkIds = data[0];
/******/ 			var moreModules = data[1];
/******/ 			var runtime = data[2];
/******/ 			// add "moreModules" to the modules object,
/******/ 			// then flag all "chunkIds" as loaded and fire callback
/******/ 			var moduleId, chunkId, i = 0;
/******/ 			if(chunkIds.some(function(id) { return installedChunks[id] !== 0; })) {
/******/ 				for(moduleId in moreModules) {
/******/ 					if(__webpack_require__.o(moreModules, moduleId)) {
/******/ 						__webpack_require__.m[moduleId] = moreModules[moduleId];
/******/ 					}
/******/ 				}
/******/ 				if(runtime) var result = runtime(__webpack_require__);
/******/ 			}
/******/ 			if(parentChunkLoadingFunction) parentChunkLoadingFunction(data);
/******/ 			for(;i < chunkIds.length; i++) {
/******/ 				chunkId = chunkIds[i];
/******/ 				if(__webpack_require__.o(installedChunks, chunkId) && installedChunks[chunkId]) {
/******/ 					installedChunks[chunkId][0]();
/******/ 				}
/******/ 				installedChunks[chunkId] = 0;
/******/ 			}
/******/ 			return __webpack_require__.O(result);
/******/ 		}
/******/ 		
/******/ 		var chunkLoadingGlobal = self["webpackChunk"] = self["webpackChunk"] || [];
/******/ 		chunkLoadingGlobal.forEach(webpackJsonpCallback.bind(null, 0));
/******/ 		chunkLoadingGlobal.push = webpackJsonpCallback.bind(null, chunkLoadingGlobal.push.bind(chunkLoadingGlobal));
/******/ 	}();
/******/ 	
/************************************************************************/
/******/ 	
/******/ 	// startup
/******/ 	// Load entry module and return exports
/******/ 	// This entry module depends on other loaded chunks and execution need to be delayed
/******/ 	__webpack_require__.O(undefined, ["css/app","admin_assets/dist/css/admin","front/css/styles"], function() { return __webpack_require__("./resources/js/front/main.js"); })
/******/ 	__webpack_require__.O(undefined, ["css/app","admin_assets/dist/css/admin","front/css/styles"], function() { return __webpack_require__("./resources/css/front/styles.scss"); })
/******/ 	__webpack_require__.O(undefined, ["css/app","admin_assets/dist/css/admin","front/css/styles"], function() { return __webpack_require__("./resources/css/admin/admin.scss"); })
/******/ 	var __webpack_exports__ = __webpack_require__.O(undefined, ["css/app","admin_assets/dist/css/admin","front/css/styles"], function() { return __webpack_require__("./resources/css/app.css"); })
/******/ 	__webpack_exports__ = __webpack_require__.O(__webpack_exports__);
/******/ 	
/******/ })()
;