/* (C)opyright 2014, Carrotsrc.org
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
		this.requestGroups();
	}

	this.requestGroups = function () {
		self._loop.request(1, null, self._loop.onresponseGroups);
	}

	this.rqdelay = function (callback) {
		setTimeout(callback, 250);
	}

	this.onresponseGroups = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			return;
		
		self.uio_groupList(response.data);
		if(self.edges == null) {
			self.rqdelay(self._loop.requestEdges);
		}
	}

	this.requestEdges =  function () {
		self._loop.request(4, null, self._loop.onresponseEdges);
	}

	this.onresponseEdges = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			return;

		self.edges = response.data;
	}

	this.requestDetails = function (groupId) {
		var str = "gid="+groupId;
		self._loop.request(2, str, self._loop.onresponseDetails);
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
		var str = "gid="+self._loop.gId+"&rid="+id;
		self._loop.request(10, str, self._loop.onresponseUserRemove);
	}

	this.onresponseUserRemove = function () {
		self._loop.requestDetails(self._loop.gId);
	}

	this.requestDropResources = function (id, edge) {
		var str = "gid="+id;
		if(edge !== undefined)
			str += "&edge="+edge

		self._loop.request(101, str, self.onresponseDropResources);
	}

	this.onresponseDropResources =  function () {
		self._loop.requestDetails(self._loop.gId);
	}

	this.requestPickupResource = function (str) {
		self._loop.request(100, str, self.onresponsePickupResource);
	}

	this.onresponsePickupResource = function () {
		ResourceBin.requestRefresh();
	}

	this.requestAddGroup = function (name) {
		self._loop.request(3, "", self.onresponseAddGroup, "gname="+name);
	}

	this.onresponseAddGroup = function () {
		self._loop.requestGroups();
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
				u.node.groupName = list[i].name;

				u.node.onclick = function () {
					self._loop.requestDetails(this.groupId);
					self._loop.gName = this.groupName;
					self._loop.gId = this.groupId;
				}

				u.appendText(list[i].name);
				u.gotoParent();
		}
		u.appendChild('div');
			u.gotoLast();
			u.node.className='item';
			u.node.onclick = self._loop.uio_newGroup;

			u.appendText("New Group");
			u.gotoParent();

		var c = document.getElementById(self._loop._pmod+"-grouplist");
		u = KTSet.NodeUtl(document.getElementById(self._loop._pmod+"-grouplist"));
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
				u.node.id = self._loop._pmod+"-addname";
				u.node.className = "vform-text";
				u.gotoParent();
			u.appendText(" ");

			u.appendChild('input');
				u.gotoLast();
				u.node.type="button";
				u.node.value="Go";
				u.node.className = "vform-text";
				u.node.onclick = function () {
					var e = document.getElementById(self._loop._pmod+"-addname");
					self._loop.requestAddGroup(e.value);
				}
				u.gotoParent();
			u.gotoParent();

		u = KTSet.NodeUtl(document.getElementById(self._loop._pmod+"-details"));
		u.clearChildren();
		u.appendChild(r);

		u = KTSet.NodeUtl(document.getElementById(self._loop._pmod+"-gname"));
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
					u.appendText(list[i].first+" "+list[i].last);
					u.gotoParent();
				u.appendText(" ("+list[i].label+")");

				u.appendChild('div');
					u.gotoLast();
					u.node.className = "vp-gm-tool";
					u.appendChild('img');
						u.gotoLast();
						u.node.src=self._loop.media+"/del_rd32.png";
						u.node.title = "Remove user";
						u.node.ridu = list[i].id;
						u.node.onclick = function () {
							self._loop.requestUserRemove(this.ridu);
						}
						u.node.onmousedown = function () {
							this.src=self._loop.media+"/del_lrd32.png";
						}

						u.node.onmouseup = function () {
							this.src=self._loop.media+"/del_rd32.png";
						}
					u.gotoParent();
				u.gotoParent();

				u.appendChild('div');
					u.gotoLast();
					u.node.className = "vp-gm-tool";
					u.appendChild('img');
						u.gotoLast();
						u.node.src=self._loop.media+"/pickup_bl32.png";
						u.node.title = "Pick up user";
						u.node.onmousedown = function () {
							this.src=self._loop.media+"/pickup_lbl32.png";
						}

						u.node.onmouseup = function () {
							this.src=self._loop.media+"/pickup_bl32.png";
						}

						u.node.rid = list[i].id;
						u.node.onclick = function () {
							self._loop.requestPickupResource("urid="+this.rid);
						}
					u.gotoParent();
				u.gotoParent();
				
				u.gotoParent();
		}

			u.gotoParent();
		u = KTSet.NodeUtl(document.getElementById(self._loop._pmod+"-details"));
		u.clearChildren();
		u.appendChild(r);

		u = KTSet.NodeUtl(document.getElementById(self._loop._pmod+"-gname"));
		u.clearChildren();
		u.appendText(self._loop.gName);


		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vp-gm-tool";
			u.appendChild('img');
				u.gotoLast();
				u.node.src=self._loop.media+"/pickup_gy32.png";
				u.node.title = "Pick up group";
				u.node.onmousedown = function () {
					this.src=self._loop.media+"/pickup_lgy32.png";
				}

				u.node.onmouseup = function () {
					this.src=self._loop.media+"/pickup_gy32.png";
				}

				u.node.onclick = function () {
					self._loop.requestPickupResource("grid="+self._loop.gId);
				}
			u.gotoParent();
		u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vp-gm-tool";
			u.appendChild('img');
				u.gotoLast();
				u.node.src=self._loop.media+"/drop_bl32.png";
				u.node.title = "Drop user into group";
				u.node.onmousedown = function () {
					this.src=self._loop.media+"/drop_lbl32.png";
				}

				u.node.onmouseup = function () {
					this.src=self._loop.media+"/drop_bl32.png";
				}

				u.node.onclick = function () {
					var edge = undefined;
					var e = document.getElementById(self._loop._pmod+"-edge-type");
					if(e !== undefined) {
						edge = e.value;
					}

					self._loop.requestDropResources(self._loop.gId, edge);
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
					u.node.id = self._loop._pmod+"-edge-type";
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

