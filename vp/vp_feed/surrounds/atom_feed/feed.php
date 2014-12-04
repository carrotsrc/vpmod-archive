<?php
/* Copyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

 //^^^ hardly worth the license... (cfg)

 	// this surround will always be sending out xml
	header("Content-type: text/xml; charset=utf-8");

	/* 
	*  we don't bother with a layout because, due to a horrible
	*  hack, the feed panel will immediately echo the feed
	*  so it conforms to the standard without being surrounded
	*  by layout containers. This is not how vegpatch is meant
	*  to be used.
	*
	*  TODO:
	*  work out an elegent solution for allowing other formatting
	*  within a layout
	*/
?>
