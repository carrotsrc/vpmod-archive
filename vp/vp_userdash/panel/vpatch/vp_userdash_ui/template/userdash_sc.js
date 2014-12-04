/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

function vp_userdashInterface() {

	var self = this;
	this.uName = "";
	this.uId = 0;
	this.pcom = new Array();
	// this really needs to be fixed
	this.media = "APP-MEDIAgeneral";

	this.initialize = function () {
		self.modifyLink('nusr', self.uio_newUser);
		// run a delay until request queue is fixed
		this.rqdelay(this.requestUsers);
	}

	this.requestUsers = function () {
		self.request(1, null, self.onresponseUsers);
	}

	this.rqdelay = function (callback) {
		setTimeout(callback, 250);
	}

	this.onresponseUsers = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 104)
			response.data = Array();
		
		self.uio_userList(response.data);
	}


	this.requestDetails = function (userId) {
		var str = "id="+userId;
		self.request(2, str, self.onresponseDetails);
	}


	this.onresponseDetails = function (reply) {
		var response = JSON.parse(reply);
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

	this.requestPickupResource = function (id) {
		var str = "krid="+id;
		self.request(100, str, self.onresponsePickupResource);
	}

	this.onresponsePickupResource = function () {
		ResourceBin.requestRefresh();
	}

	this.requestDropResources = function () {
		self.request(101, null, self.onresponseDropResources);
	}

	this.onresponseDropResources = function () {
		ResourceBin.requestRefresh();
		self.requestUsers();
	}

	this.requestAddUser = function (user, pass, email) {
		var pstr = "user="+user+"&pass="+pass+"&email="+email;
		self.request(11, null, self.onresponseAddUser, pstr);
	}

	this.onresponseAddUser = function (reply) {
		var response = JSON.parse(reply);
		if(response.code == 102) {
			self.getElementById('user').value = "";
			self.getElementById('pass').value = "";
			self.getElementById('passchk').value = "";
			self.getElementById('email').value = "";
			var u = KTSet.NodeUtl(self.getElementById('msg'));
			u.clearChildren();
			u.appendText("User created successfully");
			self.requestUsers();
		}
		else {
			var u = KTSet.NodeUtl(self.getElementById('msg'));
			u.clearChildren();
			u.appendText("Failed to create user");
		}
	}

	this.uio_userList = function (list) {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);
		var sz = list.length;

		for(var i = 0; i < sz; i++) {
			u.appendChild('div');
				u.gotoLast();
				u.node.className='item';
				u.node.userId = list[i].id;
				u.node.userName = list[i].label;

				u.node.onclick = function () {
					self.requestDetails(this.userId);
					self.uName = this.userName;
					self.uId = this.userId;
				}

				u.appendText(list[i].first+" "+list[i].last+" ("+list[i].label+")");
				u.gotoParent();
		}

		u.appendChild('div');
			u.gotoLast();
			u.node.className='item';
			u.node.onclick = function () {
				self.uio_newUser();
			}

			u.appendText("New User");
			u.gotoParent();

		u = KTSet.NodeUtl(self.getElementById("userlist"));
		u.clearChildren();
		u.appendChild(r);
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
				u.node.style = "height: 70px;";
				u.appendChild('img');
					u.gotoLast();
					u.node.src=list[i].avatar;
					u.node.style = "width: 64px; height: 64px; float: left; margin-right: 10px;";
					u.gotoParent();
				u.appendChild('span');
					u.gotoLast();
					u.node.className = "name";
					u.appendText(list[i].first+" "+list[i].last);
					u.gotoParent();
				u.appendChild('div');
				u.appendText(" ("+list[i].label+")");


				u.appendChild('div');
					u.gotoLast();
					u.node.className = "vp-ud-tool";
					u.appendChild('img');
						u.gotoLast();
						u.node.src=self.media+"/pickup_bl32.png";
						u.node.title = "Pick up user";
						u.node.rid = list[i].id;

						u.node.onclick = function () {
							self.requestPickupResource(this.rid);
						}

						u.node.onmousedown = function () {
							this.src=self.media+"/pickup_lbl32.png";
						}

						u.node.onmouseup = function () {
							this.src=self.media+"/pickup_bl32.png";
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
			u.node.className = "vp-ud-tool";
			u.appendChild('img');
				u.gotoLast();
				u.node.src=self.media+"/drop_bl32.png";
				u.node.title = "Drop user resources here";
				u.node.onmousedown = function () {
					this.src=self.media+"/drop_lbl32.png";
				}

				u.node.onmouseup = function () {
					this.src=self.media+"/drop_bl32.png";
				}

				u.node.onclick = function () {
					self.requestDropResources();
				}
			u.gotoParent();
		u.gotoParent();

	}

	this.uio_newUser = function () {
		var r = document.createDocumentFragment();
		var u = KTSet.NodeUtl(r);

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vform-item";
			u.appendText("Username: ");
			u.appendChild('br');
			u.appendInput('text', null);
				u.gotoLast();
				u.node.className = "vform-text";
				u.node.id=self.elementId('user');
				u.gotoParent();
			u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vform-item vfloat-left";
			u.node.style="margin-right: 10px;";
			u.appendText("Password: ");
			u.appendChild('br');
			u.appendInput('password', null);
				u.gotoLast();
				u.node.className = "vform-text";
				u.node.id=self.elementId('pass');
				u.gotoParent();
			u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vform-item";
			u.appendText("Retype password: ");
			u.appendChild('br');
			u.appendInput('password', null);
				u.gotoLast();
				u.node.className = "vform-text";
				u.node.id=self.elementId('passchk');
				u.gotoParent();
			u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vform-item";
			u.appendText("Email: ");
			u.appendChild('br');
			u.appendInput('text', null);
				u.gotoLast();
				u.node.className = "vform-text";
				u.node.id=self.elementId('email');
				u.gotoParent();
			u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.className = "vform-item";
			u.appendInput('button', "Create User");
				u.gotoLast();
				u.node.className = "vform-button";
				u.node.onclick = function () {
					var user = self.getElementById('user').value;
					var pass = self.getElementById('pass').value;
					var chk = self.getElementById('passchk').value;
					var email = self.getElementById('email').value;
					if(pass != chk) {
						var u = KTSet.NodeUtl(self.getElementById('msg'));
						u.clearChildren();
						u.appendText("Passwords do not match!");
						return;
					}

					if(user == undefined || user == "" ||
					pass == undefined || pass == "" ||
					email == undefined || email == "") {
						var u = KTSet.NodeUtl(self.getElementById('msg'));
						u.clearChildren();
						u.appendText("Not all the form is complete");
						return;

					}

					self.requestAddUser(user, pass, email);
				}
				u.gotoParent();
			u.gotoParent();

		u.appendChild('div');
			u.gotoLast();
			u.node.id = self.elementId('msg');
			u.gotoParent();

		u = KTSet.NodeUtl(self.getElementById('details'));
		u.clearChildren();
		u.appendChild(r);

		u = KTSet.NodeUtl(self.getElementById("gname"));
		u.clearChildren();
		u.appendText("New");
	}

}

vp_userdashInterface.prototype = new VPLib.PanelInterface('vp_userdash_ui');

