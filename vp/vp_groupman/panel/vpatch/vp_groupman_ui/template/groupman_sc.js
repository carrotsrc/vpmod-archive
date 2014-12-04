/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

function OK_GroupmanInterface() {

	var self = this;
	this.gName = "";
	this.gId = 0;
	this.pcom = new Array();
	this.edges = null;
	// this really needs to be fixed
	this.media = "APP-MEDIAgeneral";

	this.initialize = function () {
		this.modifyLink("ngp", self.uio_newGroup);
		this.requestGroups();
	}

	this.requestGroups = function () {
		self.request(1, null, self.onresponseGroups);
	}

	this.rqdelay = function (callback) {
		setTimeout(callback, 250);
	}

	this.onresponseGroups = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			response.data = Array();
		
		self.uio_groupList(response.data);
		if(self.edges == null) {
			self.rqdelay(self.requestEdges);
		}
	}

	this.requestEdges =  function () {
		self.request(4, null, self.onresponseEdges);
	}

	this.onresponseEdges = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			return;

		self.edges = response.data;
	}

	this.requestDetails = function (groupId) {
		var str = "gid="+groupId;
		self.request(2, str, self.onresponseDetails);
	}


	this.onresponseDetails = function (reply) {
		var response = JSON.parse(reply);
		ResourceBin.requestRefresh();
		if(response.code == 104) {
			self.uio_details(Array());
			return;
		}

		self.uio_details(response.data);
	}

	this.requestUserRemove = function (id) {
		var str = "gid="+self.gId+"&rid="+id;
		self.request(10, str, self.onresponseUserRemove);
	}

	this.onresponseUserRemove = function () {
		self.requestDetails(self.gId);
	}

	this.requestDropResources = function (id, edge) {
		var str = "gid="+id;
		if(edge !== undefined)
			str += "&edge="+edge

		self.request(101, str, self.onresponseDropResources);
	}

	this.onresponseDropResources =  function () {
		self.requestDetails(self.gId);
	}

	this.requestPickupResource = function (str) {
		self.request(100, str, self.onresponsePickupResource);
	}

	this.onresponsePickupResource = function () {
		ResourceBin.requestRefresh();
	}

	this.requestAddGroup = function (name) {
		self.request(3, "", self.onresponseAddGroup, "gname="+name);
	}

	this.onresponseAddGroup = function () {
		self.requestGroups();
	}

	this.uio_groupList = function (list) {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		var sz = list.length;

		for(var i = 0; i < sz; i++) {
			u.appendChild('div');
				u.gotoLast();
				u.node.className='item';
				u.node.groupId = list[i].id;
				u.node.groupName = list[i].label;

				u.node.onclick = function () {
					self.requestDetails(this.groupId);
					self.gName = this.groupName;
					self.gId = this.groupId;
				}

				u.appendText(list[i].label);
				u.gotoParent();
		}
		u.appendChild('div');
			u.gotoLast();
			u.node.className='item';
			u.node.onclick = self.uio_newGroup;

			u.appendText("New Group");
			u.gotoParent();

		var c = document.getElementById(self._pmod+"-grouplist");
		u = KTSet.NodeUtl(document.getElementById(self._pmod+"-grouplist"));
		u.clearChildren();
		u.appendChild(r);
	}

	this.uio_newGroup = function () {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		u.appendChild('div');
			u.gotoLast();
			u.node.className = "list";
			u.appendText("Group Name: ");
			u.appendChild('input');
				u.gotoLast();
				u.node.id = self._pmod+"-addname";
				u.node.className = "vform-text";
				u.gotoParent();
			u.appendText(" ");

			u.appendChild('input');
				u.gotoLast();
				u.node.type="button";
				u.node.value="Go";
				u.node.className = "vform-text";
				u.node.onclick = function () {
					var e = document.getElementById(self._pmod+"-addname");
					self.requestAddGroup(e.value);
				}
				u.gotoParent();
			u.gotoParent();

		u = KTSet.NodeUtl(document.getElementById(self._pmod+"-details"));
		u.clearChildren();
		u.appendChild(r);

		u = KTSet.NodeUtl(document.getElementById(self._pmod+"-gname"));
		u.clearChildren();
		u.appendText("New");

	}

	this.uio_details = function (list) {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		var sz = list.length;
		u.appendChild('div');
			u.gotoLast();
			u.node.className='list';

		for(var i = 0; i < sz; i++) {
			u.appendChild('div');
				u.gotoLast();
				u.node.className='item';
				u.appendChild('span');
					u.gotoLast();
					u.node.className = "name";
					u.appendText(list[i].label);
					u.gotoParent();

				u.appendChild('div');
					u.gotoLast();
					u.node.className = "vp-gm-tool";
					u.appendChild('img');
						u.gotoLast();
						u.node.src=self.media+"/del_rd32.png";
						u.node.title = "Remove user";
						u.node.ridu = list[i].id;
						u.node.onclick = function () {
							self.requestUserRemove(this.ridu);
						}
						u.node.onmousedown = function () {
							this.src=self.media+"/del_lrd32.png";
						}

						u.node.onmouseup = function () {
							this.src=self.media+"/del_rd32.png";
						}
					u.gotoParent();
				u.gotoParent();

				u.appendChild('div');
					u.gotoLast();
					u.node.className = "vp-gm-tool";
					u.appendChild('img');
						u.gotoLast();
						u.node.src=self.media+"/pickup_bl32.png";
						u.node.title = "Pick up user";
						u.node.onmousedown = function () {
							this.src=self.media+"/pickup_lbl32.png";
						}

						u.node.onmouseup = function () {
							this.src=self.media+"/pickup_bl32.png";
						}

						u.node.rid = list[i].id;
						u.node.onclick = function () {
							self.requestPickupResource("urid="+this.rid);
						}
					u.gotoParent();
				u.gotoParent();
				
				u.gotoParent();
		}

			u.gotoParent();
		u = KTSet.NodeUtl(document.getElementById(self._pmod+"-details"));
		u.clearChildren();
		u.appendChild(r);

		u = KTSet.NodeUtl(document.getElementById(self._pmod+"-gname"));
		u.clearChildren();
		u.appendText(self.gName);


		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vp-gm-tool";
			u.appendChild('img');
				u.gotoLast();
				u.node.src=self.media+"/pickup_gy32.png";
				u.node.title = "Pick up group";
				u.node.onmousedown = function () {
					this.src=self.media+"/pickup_lgy32.png";
				}

				u.node.onmouseup = function () {
					this.src=self.media+"/pickup_gy32.png";
				}

				u.node.onclick = function () {
					self.requestPickupResource("grid="+self.gId);
				}
			u.gotoParent();
		u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vp-gm-tool";
			u.appendChild('img');
				u.gotoLast();
				u.node.src=self.media+"/drop_bl32.png";
				u.node.title = "Drop user into group";
				u.node.onmousedown = function () {
					this.src=self.media+"/drop_lbl32.png";
				}

				u.node.onmouseup = function () {
					this.src=self.media+"/drop_bl32.png";
				}

				u.node.onclick = function () {
					var edge = undefined;
					var e = document.getElementById(self._pmod+"-edge-type");
					if(e !== null) {
						edge = e.value;
					}

					self.requestDropResources(self.gId, edge);
				}
			u.gotoParent();
		u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vp-gm-tool";
			if(self.edges != null && self.edges.length > 0) {
				u.appendChild('select');
					u.gotoLast();
					u.node.className = "vform-text vform-select vfont-large";
					u.node.id = self._pmod+"-edge-type";
					u.node.style = "margin-right: 7px;";
					var sz = self.edges.length;
						u.appendChild('option');
							u.gotoLast();
							u.node.value="0";
							u.appendText("normal");
							u.gotoParent();
					for(var i = 0; i < sz; i++) {
						u.appendChild('option');
							u.gotoLast();
							u.node.value=self.edges[i].id;
							u.appendText(self.edges[i].edge);
							u.gotoParent();
					}
					u.gotoParent();
			}
			u.gotoParent();

	}

}

OK_GroupmanInterface.prototype = new KitJS.PanelInterface('vp_groupman_ui');

