(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["SingleWidgetManager"] = factory();
	else
		root["SingleWidgetManager"] = factory();
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
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
/******/ 	return __webpack_require__(__webpack_require__.s = 3);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var SubscribeTopic;
(function (SubscribeTopic) {
    SubscribeTopic["SEARCH_REQUEST"] = "SEARCH_REQUEST";
    SubscribeTopic["SEARCH_FAILED"] = "SEARCH_FAILED";
    SubscribeTopic["COLLECT_POINT_SELECTED"] = "COLLECT_POINT_SELECTED";
    SubscribeTopic["COLLECT_POINT_UNSELECTED"] = "COLLECT_POINT_UNSELECTED";
    SubscribeTopic["COLLECT_POINT_CONFIRMED"] = "COLLECT_POINT_CONFIRMED";
    SubscribeTopic["BOOT_FAILED"] = "BOOT_FAILED";
    SubscribeTopic["WIDGET_READY"] = "WIDGET_READY";
})(SubscribeTopic = exports.SubscribeTopic || (exports.SubscribeTopic = {}));
var EmitTopic;
(function (EmitTopic) {
    EmitTopic["INVALIDATE_MAP_SIZE"] = "INVALIDATE_MAP_SIZE";
    EmitTopic["MAP_SEARCH_QUERY"] = "MAP_SEARCH_QUERY";
    EmitTopic["MAP_LATLNG_QUERY"] = "MAP_LATLNG_QUERY";
    EmitTopic["RESET_STATE"] = "RESET_STATE";
    EmitTopic["SHOW_STATIC_MAP"] = "SHOW_STATIC_MAP";
    EmitTopic["SHOW_INFORMATION"] = "SHOW_INFORMATION";
    EmitTopic["SET_CONFIGURATION"] = "SET_CONFIGURATION";
    EmitTopic["SET_CONFIGURATION_THEME"] = "SET_CONFIGURATION_THEME";
})(EmitTopic = exports.EmitTopic || (exports.EmitTopic = {}));


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
Object.defineProperty(exports, "__esModule", { value: true });
var MessageManager_1 = __webpack_require__(4);
var PubSubManager_1 = __webpack_require__(5);
var IframeBuilder_1 = __webpack_require__(8);
var windowTopicsObject_1 = __webpack_require__(9);
var singleWidget_1 = __webpack_require__(10);
var topics_1 = __webpack_require__(0);
var HealthCheck_1 = __webpack_require__(11);
var messageManager_1 = __webpack_require__(2);
/**
 * SingleWidgetManager
 *
 * Provides public api to user
 *
 * With:
 * Options originally passed to the constructor.
 * Events to pass callbacks to.
 * Topics to pass to Events
 *
 */
var SingleWidgetManager = /** @class */ (function () {
    function SingleWidgetManager(options) {
        if (options === void 0) { options = {}; }
        var eventManager = new PubSubManager_1.default();
        var defaultWidgetOptions = {
            container: document.body,
            iframeParams: {},
            iframeUrl: null,
            isDebug: false,
            healthCheck: true
        };
        if (options.container !== undefined &&
            !(options.container instanceof Element)) {
            throw Error(singleWidget_1.ERROR_MESSAGES.CONTAINER_NOT_ELEMENT);
        }
        var finalOptions = __assign(__assign(__assign({}, defaultWidgetOptions), options), {
            iframeParams: __assign(__assign({}, options.iframeParams), { managerVersion: SingleWidgetManager.meta.version })
        });
        if (typeof finalOptions.iframeUrl === "string") {
            try {
                new URL(finalOptions.iframeUrl);
            }
            catch (e) {
                throw Error(singleWidget_1.ERROR_MESSAGES.URL_PARSE_ERROR);
            }
        }
        else {
            throw Error(singleWidget_1.ERROR_MESSAGES.IFRAME_URL_REQUIRED);
        }
        var iframeURL = new URL(finalOptions.iframeUrl);
        var iframeBuilder = new IframeBuilder_1.default(finalOptions.iframeParams, iframeURL.origin, iframeURL.pathname);
        var iframeEl = iframeBuilder.getIframeEl();
        var messageManager = new MessageManager_1.default(eventManager, iframeEl, iframeURL.origin, finalOptions.isDebug);
        var getHealthCheckTimeoutParam = function () {
            var _a;
            if (typeof finalOptions.healthCheck !== "boolean") {
                return (_a = finalOptions === null || finalOptions === void 0 ? void 0 : finalOptions.healthCheck) === null || _a === void 0 ? void 0 : _a.timeout;
            }
            return undefined;
        };
        if (finalOptions.healthCheck) {
            HealthCheck_1.default.healthCheck(iframeURL.origin, finalOptions.isDebug, getHealthCheckTimeoutParam()).catch(function (err) {
                if (finalOptions.isDebug) {
                    console.error(singleWidget_1.ERROR_MESSAGES.HEALTH_CHECK_FAILED, err);
                }
                messageManager.abort();
                messageManager.eventManager.emit(topics_1.SubscribeTopic.BOOT_FAILED, {
                    error: messageManager_1.BOOT_FAIL_CODES.HEALTH_CHECK_FAILED
                });
            });
        }
        if (iframeEl) {
            if (finalOptions.container) {
                finalOptions.container.append(iframeEl);
            }
            else {
                throw Error(singleWidget_1.ERROR_MESSAGES.CONTAINER_NOT_FOUND);
            }
        }
        else {
            throw Error(singleWidget_1.ERROR_MESSAGES.IFRAME_NOT_FOUND);
        }
        var widgetPublicApi = {
            events: __assign(__assign({}, eventManager.getConsumerMethods()), { emit: function (topic, message) {
                    messageManager.queueMessageToIframe({
                        topic: topic,
                        message: message
                    });
                    return this;
                } }),
            topics: windowTopicsObject_1.windowTopicsObject
        };
        this.events = widgetPublicApi.events;
        this.topics = widgetPublicApi.topics;
    }
    SingleWidgetManager.meta = {
        commitHash: 'ef67801',
        version: '2.5.5'
    };
    SingleWidgetManager.iframeUrls = {
        SANDBOX: "https://sandbox.frame.hub-box.com",
        PRODUCTION: "https://frame.hub-box.com"
    };
    return SingleWidgetManager;
}());
exports.SingleWidgetManager = SingleWidgetManager;


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.ERROR_MESSAGES = {
    IFRAME_NOT_FOUND: "The Iframe cannot be found in the DOM",
    PROBLEM_PARSING_MESSAGE: "There was a problem parsing the message",
    INTERVAL_NOT_DEFINED: "Response Interval is not defined",
    EXCEEDED_MAX_HANDSHAKES: "Exceeded max number of handshake retries"
};
exports.DEBUG_MESSAGES = {
    TOPIC_NOT_FOUND: "The Topic sent in iframe message cannot be found"
};
exports.BOOT_FAIL_CODES = {
    EXCEEDED_MAX_HANDSHAKES: "EXCEEDED_MAX_HANDSHAKES",
    HEALTH_CHECK_FAILED: "HEALTH_CHECK_FAILED"
};
exports.HANDSHAKE_MESSAGE = "handshake";
exports.HANDSHAKE_REPLY_MESSAGE = "handshake-reply";


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var SingleWidgetManager_1 = __webpack_require__(1);
exports.default = SingleWidgetManager_1.SingleWidgetManager;


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var topics_1 = __webpack_require__(0);
var messageManager_1 = __webpack_require__(2);
/**
 * Message Manager
 *
 * Manages messages to and from the iframe
 *
 */
var MessageManager = /** @class */ (function () {
    function MessageManager(eventManager, iframeEl, iframeOrigin, isDebug) {
        if (isDebug === void 0) { isDebug = false; }
        this.eventManager = eventManager;
        this.iframeEl = iframeEl;
        this.iframeOrigin = iframeOrigin;
        this.isDebug = isDebug;
        this.eventManager = eventManager;
        this.iframeEl = iframeEl;
        this.iframeOrigin = iframeOrigin;
        this.isDebug = isDebug;
        this.channel = new MessageChannel();
        this.numOfHandshakeAttempts = 0;
        this.maxNumOfHandshakeAttempts = 5;
        this.intervalTime = 500;
        this.messageQueue = [];
        this.handshakeReceived = false;
        this.aborted = false;
        this.setUpHandshakeOnLoad();
    }
    MessageManager.prototype.sendHandshakeToIframeWindow = function () {
        if (!this.iframeEl || !this.iframeEl.contentWindow) {
            throw Error(messageManager_1.ERROR_MESSAGES.IFRAME_NOT_FOUND);
        }
        try {
            this.setUpMessageListener();
            this.iframeEl.contentWindow.postMessage(messageManager_1.HANDSHAKE_MESSAGE, this.iframeOrigin, [this.channel.port2]);
        }
        catch (e) {
            if (this.isDebug) {
                console.warn("Post message failed. " + (this.maxNumOfHandshakeAttempts -
                    this.numOfHandshakeAttempts) + " attempts left");
            }
        }
    };
    MessageManager.prototype.abort = function () {
        this.aborted = true;
    };
    MessageManager.prototype.setUpMessageListener = function () {
        // Before Each Handshake we set up a new Channel.
        // Once a port is transferred it cannot be reused
        // If we are attempting a handshake again we close the port
        this.channel.port2.close();
        this.channel.port1.close();
        this.channel = new MessageChannel();
        this.channel.port1.onmessage = this.handShakeReplyCallback.bind(this);
    };
    MessageManager.prototype.queueMessageToIframe = function (messageAndTopic) {
        if (!this.handshakeReceived) {
            this.messageQueue.push(messageAndTopic);
        }
        else {
            this.channel.port1.postMessage(messageAndTopic);
        }
    };
    MessageManager.prototype.performHandshake = function () {
        if (!this.responseInterval) {
            throw Error(messageManager_1.ERROR_MESSAGES.INTERVAL_NOT_DEFINED);
        }
        if (this.numOfHandshakeAttempts >= this.maxNumOfHandshakeAttempts ||
            this.aborted) {
            clearInterval(this.responseInterval);
            if (!this.aborted) {
                this.eventManager.emit(topics_1.SubscribeTopic.BOOT_FAILED, {
                    error: messageManager_1.BOOT_FAIL_CODES.EXCEEDED_MAX_HANDSHAKES
                });
            }
            return;
        }
        this.numOfHandshakeAttempts++;
        this.sendHandshakeToIframeWindow();
    };
    MessageManager.prototype.setUpHandshakeOnLoad = function () {
        var _this = this;
        var callback = function () {
            _this.responseInterval = setInterval(_this.performHandshake.bind(_this), _this.intervalTime);
            _this.performHandshake();
        };
        // Chrome and Edge will emit a load event on an iframe that does
        // not have a status code 200, Safari and FF will not.
        // So in the instance of a non 200 status on frame BOOT_FAILED
        // will not trigger for Safari and FF.
        // However we also have the health check to cater for Safari and FF when frame responds
        // with a non 200. This will also trigger BOOT_FAILED if frame is down.
        // Safari and FF may not receive a BOOT_FAILED event if frame has a 200
        // but the widget CDN is down
        this.iframeEl.addEventListener("load", callback.bind(this));
    };
    MessageManager.prototype.handShakeReplyCallback = function (event) {
        var _this = this;
        if (event.data === messageManager_1.HANDSHAKE_REPLY_MESSAGE) {
            this.handshakeReceived = true;
            if (this.responseInterval) {
                clearInterval(this.responseInterval);
            }
            else {
                throw Error(messageManager_1.ERROR_MESSAGES.INTERVAL_NOT_DEFINED);
            }
            this.channel.port1.onmessage = this.messageCallback.bind(this);
            this.messageQueue.forEach(function (messageAndTopic) {
                _this.channel.port1.postMessage(messageAndTopic);
            });
        }
    };
    MessageManager.prototype.messageCallback = function (event) {
        var iFrameMessageData = event.data;
        if (iFrameMessageData.topic in topics_1.SubscribeTopic) {
            this.eventManager.emit(iFrameMessageData.topic, iFrameMessageData.message);
        }
        else {
            if (this.isDebug) {
                console.debug(messageManager_1.DEBUG_MESSAGES.TOPIC_NOT_FOUND);
            }
        }
    };
    return MessageManager;
}());
exports.default = MessageManager;


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var topics_1 = __webpack_require__(0);
var pubSubManager_1 = __webpack_require__(6);
var ConsumerMethods_1 = __webpack_require__(7);
/**
 * Manages messages and topics so listeners
 * can be added to (and removed from) events
 * emitted from the iframe
 *
 * Wraps an EventTarget mapping Topic to Event Type
 * and Message to Event Detail
 *
 * As safari does not support Event Target constructor we provide a fallback and use a DOM element
 *
 */
var PubSubManager = /** @class */ (function () {
    function PubSubManager(target) {
        if (target === void 0) { target = PubSubManager.getEventTargetWithFallback(); }
        this.target = target;
        this.subscribedEvents = Object.keys(topics_1.SubscribeTopic).reduce(function (acc, curr) {
            acc[curr] = [];
            return acc;
        }, {});
        this.target = target;
    }
    PubSubManager.prototype.subscribe = function (topic, cb, once) {
        var _this = this;
        if (once === void 0) { once = false; }
        if (topic in topics_1.SubscribeTopic) {
            var listener_1 = (function (evt) {
                return cb({ topic: topic, message: evt.detail });
            });
            this.target.addEventListener(topic, listener_1, { once: once });
            this.subscribedEvents[topic].push([
                cb,
                function () { return _this.target.removeEventListener(topic, listener_1); }
            ]);
            return this;
        }
        else {
            throw Error(pubSubManager_1.TOPIC_ERROR_MESSAGE);
        }
    };
    PubSubManager.prototype.unsubscribe = function (topic, cb) {
        if (topic in topics_1.SubscribeTopic) {
            if (cb) {
                var foundCallbackTuple = this.subscribedEvents[topic].find(function (topicCallbackTuple) { return topicCallbackTuple[0] === cb; });
                if (foundCallbackTuple && foundCallbackTuple[1]) {
                    foundCallbackTuple[1]();
                }
            }
            else {
                this.subscribedEvents[topic].forEach(function (topicCallbackTuple) {
                    if (topicCallbackTuple && topicCallbackTuple[1]) {
                        topicCallbackTuple[1]();
                    }
                });
            }
        }
        else {
            throw Error(pubSubManager_1.TOPIC_ERROR_MESSAGE);
        }
        return this;
    };
    PubSubManager.prototype.takeFirst = function (topic, cb) {
        if (topic in topics_1.SubscribeTopic) {
            return this.subscribe(topic, cb, true);
        }
        else {
            throw Error(pubSubManager_1.TOPIC_ERROR_MESSAGE);
        }
    };
    PubSubManager.prototype.emit = function (topic, message) {
        if (topic in topics_1.SubscribeTopic) {
            this.target.dispatchEvent(new CustomEvent(topic, { detail: message }));
        }
        else {
            throw Error(pubSubManager_1.TOPIC_ERROR_MESSAGE);
        }
        return this;
    };
    /**
     * Returns consumer methods only, as you might not want to provide emit
     */
    PubSubManager.prototype.getConsumerMethods = function () {
        return ConsumerMethods_1.consumerMethodsFactory(this);
    };
    PubSubManager.getEventTargetWithFallback = function () {
        var eventTarget;
        try {
            eventTarget = new EventTarget();
        }
        catch (e) {
            eventTarget = document.createElement("div");
        }
        return eventTarget;
    };
    return PubSubManager;
}());
exports.default = PubSubManager;


/***/ }),
/* 6 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.TOPIC_ERROR_MESSAGE = "Topic cannot be found";


/***/ }),
/* 7 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.consumerMethodsFactory = function (pubSubManager) { return ({
    subscribe: function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        pubSubManager.subscribe.apply(pubSubManager, args);
        return this;
    },
    unsubscribe: function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        pubSubManager.unsubscribe.apply(pubSubManager, args);
        return this;
    },
    takeFirst: function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        pubSubManager.takeFirst.apply(pubSubManager, args);
        return this;
    }
}); };


/***/ }),
/* 8 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
/**
 * IframeBuilder
 *
 * Builds styles and configures
 * a HTMLIFrameElement
 *
 */
var IframeBuilder = /** @class */ (function () {
    function IframeBuilder(srcParameters, iframeOrigin, iframePath) {
        if (srcParameters === void 0) { srcParameters = {}; }
        this.srcParameters = srcParameters;
        this.iframeOrigin = iframeOrigin;
        this.iframePath = iframePath;
        this.iframeOrigin = iframeOrigin;
        this.srcParameters = srcParameters;
        this.iframeEl = document.createElement("iframe");
        this.setIframeSrc();
        this.setAllowGeoLocation();
        this.setNoScrolling();
        this.setNoFrameBorder();
    }
    /**
     * Sets the src on the Iframe
     * HTML element with concatenation of origin and
     * srcParameters
     */
    IframeBuilder.prototype.setIframeSrc = function () {
        if (this.hasKeys(this.srcParameters)) {
            this.iframeEl.src = "" + this.iframeOrigin + this.iframePath + "?" + this.serializeSrcParameters();
        }
        else {
            this.iframeEl.src = this.iframeOrigin + this.iframePath;
        }
    };
    IframeBuilder.prototype.setAllowGeoLocation = function () {
        this.getIframeEl().setAttribute("allow", "geolocation");
    };
    IframeBuilder.prototype.setNoScrolling = function () {
        this.getIframeEl().setAttribute("scrolling", "no");
    };
    IframeBuilder.prototype.setNoFrameBorder = function () {
        this.getIframeEl().setAttribute("frameBorder", "0");
    };
    IframeBuilder.prototype.getIframeEl = function () {
        return this.iframeEl;
    };
    IframeBuilder.prototype.getIframeOrigin = function () {
        return this.iframeOrigin;
    };
    IframeBuilder.prototype.serializeSrcParameters = function () {
        return IframeBuilder.serialise(this.srcParameters);
    };
    /**
     * Serialises object with key value
     * If ConfigDataMap property is undefined the parameter will
     * not get serialised
     *
     * @param configDataMap
     */
    IframeBuilder.serialise = function (configDataMap) {
        return Object.keys(configDataMap)
            .filter(function (key) { return configDataMap[key] !== undefined; })
            .map(function (key) {
            if (typeof configDataMap[key] === "object") {
                return [key, JSON.stringify(configDataMap[key])]
                    .map(encodeURIComponent)
                    .join("=");
            }
            return [key, configDataMap[key]]
                .map(encodeURIComponent)
                .join("=");
        })
            .join("&");
    };
    IframeBuilder.prototype.hasKeys = function (object) {
        return !!Object.keys(object).length;
    };
    return IframeBuilder;
}());
exports.default = IframeBuilder;


/***/ }),
/* 9 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var topics_1 = __webpack_require__(0);
exports.windowTopicsObject = {
    subscribe: Object.keys(topics_1.SubscribeTopic).reduce(function (acc, curr) {
        acc[curr] = curr;
        return acc;
    }, {}),
    emit: Object.keys(topics_1.EmitTopic).reduce(function (acc, curr) {
        acc[curr] = curr;
        return acc;
    }, {})
};


/***/ }),
/* 10 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.ERROR_MESSAGES = {
    CONTAINER_NOT_FOUND: "Could not find container in the DOM. Iframe could not be appended",
    IFRAME_NOT_FOUND: "Cannot find iframe element",
    CONTAINER_NOT_ELEMENT: "Container should to be a DOM element",
    URL_PARSE_ERROR: "Unable to parse iframe url",
    IFRAME_URL_REQUIRED: "Iframe url option is a required option",
    HEALTH_CHECK_FAILED: "Health check failed"
};


/***/ }),
/* 11 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
var SingleWidgetManager_1 = __webpack_require__(1);
var healthCheck_1 = __webpack_require__(12);
var HealthCheck = /** @class */ (function () {
    function HealthCheck() {
    }
    HealthCheck.healthCheck = function (iframeOrigin, isDebug, timeout) {
        if (timeout === void 0) { timeout = this.DEFAULT_TIMEOUT; }
        if (Object.values(SingleWidgetManager_1.SingleWidgetManager.iframeUrls).includes(iframeOrigin)) {
            if (isDebug) {
                console.warn(iframeOrigin + " is not in iframeUrls list, please use an iframe from SingleWidgetManager.iframeUrls");
            }
        }
        var controller = new AbortController();
        var timeoutRef = window.setTimeout(function () {
            controller.abort();
        }, timeout);
        return new Promise(function (resolve, reject) {
            return fetch(iframeOrigin + "/" + HealthCheck.URL_SLUG, {
                signal: controller.signal
            })
                .then(function (response) {
                if (response.status !== 200) {
                    throw Error(healthCheck_1.ERROR_MESSAGES.HEALTH_CHECK_STATUS);
                }
                return response.json();
            })
                .then(function (_a) {
                var status = _a.status;
                if (status !== HealthCheck.UP_STATUS) {
                    throw Error(healthCheck_1.ERROR_MESSAGES.HEALTH_CHECK_BODY);
                }
                else {
                    resolve();
                }
            })
                .catch(function (err) {
                reject(err);
            })
                .finally(function () {
                clearTimeout(timeoutRef);
            });
        });
    };
    HealthCheck.URL_SLUG = "health";
    HealthCheck.UP_STATUS = "UP";
    HealthCheck.DEFAULT_TIMEOUT = 5000;
    return HealthCheck;
}());
exports.default = HealthCheck;


/***/ }),
/* 12 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

Object.defineProperty(exports, "__esModule", { value: true });
exports.ERROR_MESSAGES = {
    HEALTH_CHECK_STATUS: "Health Check response does not have status code of 200",
    HEALTH_CHECK_BODY: "Health Check response body is not valid"
};


/***/ })
/******/ ])["default"];
});
//# sourceMappingURL=single-widget-manager.js.map