function _classCallCheck(e,n){if(!(e instanceof n))throw new TypeError("Cannot call a class as a function")}function _defineProperties(e,n){for(var t=0;t<n.length;t++){var c=n[t];c.enumerable=c.enumerable||!1,c.configurable=!0,"value"in c&&(c.writable=!0),Object.defineProperty(e,c.key,c)}}function _createClass(e,n,t){return n&&_defineProperties(e.prototype,n),t&&_defineProperties(e,t),e}(window.webpackJsonp=window.webpackJsonp||[]).push([[2],{"KP+3":function(e,n,t){"use strict";t.d(n,"a",(function(){return a}));var c=t("ToIr"),i=t("8Y7J"),a=function(){var e=function(){function e(n){_classCallCheck(this,e),this.connectionService=n}return _createClass(e,[{key:"getCities",value:function(e,n){return this.connectionService.get("admin/cities?page=".concat(e,"&limit=").concat(n))}},{key:"createCity",value:function(e){return this.connectionService.post("admin/cities",e)}},{key:"updateCity",value:function(e,n){return this.connectionService.put("admin/cities/".concat(e),n)}},{key:"deleteCity",value:function(e){return this.connectionService.delete("admin/cities/".concat(e))}},{key:"getAreas",value:function(e,n){return this.connectionService.get("admin/areas?page=".concat(e,"&limit=").concat(n))}},{key:"createArea",value:function(e){return this.connectionService.post("admin/areas",e)}},{key:"updateArea",value:function(e,n){return this.connectionService.put("admin/areas/".concat(e),n)}},{key:"deleteArea",value:function(e){return this.connectionService.delete("admin/areas/".concat(e))}},{key:"getCityAreas",value:function(e,n,t){return this.connectionService.get("store/cities/".concat(e,"/areas?page=").concat(n,"&limit=").concat(t))}}]),e}();return e.ngInjectableDef=i.jc({factory:function(){return new e(i.kc(c.a))},token:e,providedIn:"root"}),e}()}}]);