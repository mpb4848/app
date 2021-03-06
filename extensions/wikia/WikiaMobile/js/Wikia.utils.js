/**
 * This is a Wikia 'framework'
 * or rather bunch of util functions to help developing
 * WikiaMobile skin
 *
 * we decided not to use any frameworks as all we actually need is
 * addEventListener, find some elements and do some operations on array
 *
 * Frameworks like Zepto or jqMobi are nice
 * but with a cost of downloading another 9/10kb of data
 * If we don't have to load it its a win, and we don't
 *
 * This is just a place for functions that otherwise need some bigger boilerplates
 */
(function(w){
	"use strict";

	var htmlProto = HTMLElement.prototype,
		fired;

	//'polyfill' for a conistent matchesSelector
	htmlProto.matchesSelector = htmlProto.matchesSelector ||
		htmlProto.webkitMatchesSelector ||
		htmlProto.mozMatchesSelector ||
		htmlProto.oMatchesSelector;

	w.addEventListener('DOMContentLoaded', function(){
		fired = true;
	});

	var Wikia = function(func){
		fired ? func() : w.addEventListener('DOMContentLoaded', func);
	};

	Wikia.not = function(selector, elements){
		var ret = [];

		if(elements && selector){
			elements = Array.prototype.slice.call(elements);

			for(var i = 0, l = elements.length; i < l; i++){
				if(!elements[i].matchesSelector(selector)){
					ret[ret.length] = elements[i];
				}
			}
		}

		return ret;
	};

	Wikia.param = function(params){
		var ret = [];

		if(params) {
			for(var param in params){
				if(params.hasOwnProperty(param)){
					ret[ret.length] = (param + '=' + params[param]);
				}
			}
		}

		return ret.join('&');
	};

	Wikia.ajax = function(attr){
		var dfd = new Wikia.Deferred(),
			type = (attr.type && attr.type.toUpperCase()) || 'GET',
			dataType = (attr.dataType && attr.dataType.toLowerCase()) || 'json',
			data = attr.data || {},
			url = attr.url || wgScriptPath,
			async = (attr.async !== false),
			req = new XMLHttpRequest();

		if(type === 'GET' && data){
			url += (url.indexOf('?') > -1 ? '&' : '?') + Wikia.param(data);
			data = null;
		}

		req.open(type, url, async);
		req.onreadystatechange = function (ev) {
			if (req.readyState === 4) {
				var data = ev.target.responseText,
					status = req.status;

				if((status >= 200 && status < 300) || status === 304){
					dfd.resolve((dataType === 'json') ? JSON.parse(data) : data);
				} else {
					dfd.reject({
						error: data,
						status: status,
						request: req
					});
				}

			}
		};
		req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
		req.send(data ? Wikia.param(data) : null);

		return dfd.promise();
	};

	Wikia.extend = function(target){
		var args = Array.prototype.slice.call(arguments, 1),
			l = args.length,
			i = 0,
			arg;

		for(; i < l; i++){
			arg = args[i];
			for (var key in arg)
				if (arg[key] !== undefined) target[key] = arg[key];
		}

		return target;
	};

	//expose it to the world
	//$ is forbackward compatability
	w.Wikia = Wikia;
	w.$ = Wikia;

	//AMD
	if (w.define && w.define.amd) {
		w.define('wikia.utils', function() {
			return Wikia;
		});
	}

})(this, document);
