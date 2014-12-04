/* Copyright 2014, Charlie Fyvie-Gauld (Carrotsrg.org)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
function VP_RbinInterface() {

	var self = this;
	this.uName = "";
	this.uId = 0;
	this.pcom = new Array();
	// this really needs to be fixed
	this.media = "APP-MEDIAgeneral";

	this.initialize = function () {
		// run a delay until request queue is fixed
		ResourceBin.register(self);
	}

	this.refresh = function () {
		self._loop.request(1, null, self._loop.onresponseRefresh);
	}

	this.onresponseRefresh = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			return self.uic_resourceList();

		self.uio_resourceList(response.data);
	}

	this.requestRemoveResource = function (id) {
		self._loop.request(2, "rid="+id, self._loop.onresponseRemoveResource);
	}

	this.onresponseRemoveResource = function () {
		self._loop.refresh();
	}

	this.requestFlagResource = function (id) {
		self._loop.request(3, "rid="+id, self._loop.onresponseRefresh);
	}

	this.uio_resourceList = function (list) {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		var sz = list.length;
		for(var i = 0; i < sz; i++) {
			u.appendChild('div');
				u.gotoLast();
				u.node.className = "rbin-resource";
				if(list[i].flag == 0)
					u.node.style = "border-style: dashed; background-color: white;";

				u.appendChild('a');
					u.gotoLast();
					u.appendText(list[i].label+" ");
					u.node.rid = list[i].rid;
					u.node.onclick =  function () {
						self._loop.requestFlagResource(this.rid);
					}
					u.gotoParent();
				u.appendChild('a');
					u.gotoLast();
					u.appendText("X");
					u.node.rid = list[i].rid;
					u.node.onclick =  function () {
						self._loop.requestRemoveResource(this.rid);
					}
					u.gotoParent();
				u.gotoParent();
		}

		var c = document.getElementById(self._loop._pmod+"-list");
		u = KTSet.NodeUtl(c);
		u.clearChildren();
		u.appendChild(r);
	}

	this.uic_resourceList = function () {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		u.appendText("empty");

		var c = document.getElementById(self._loop._pmod+"-list");
		u = KTSet.NodeUtl(c);
		u.clearChildren();
		u.appendChild(r);
	}

}

VP_RbinInterface.prototype = new KitJS.PanelInterface('vp_rbin');

