<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */
define('CL_CTL', 1);
define('CL_STR', 2);
define('CL_TRAIL', 4);
define('CL_HEADER', 8);
define('CL_OPEN_HEADER', 16);
define('CL_LIST', 32);
define('CL_AHREF', 64);
define('CL_ATITLE', 128);
define('CL_ISRC', 256);
define('CL_IALT', 512);
define('CL_TABLE', 1024);
define('CL_TABLE_HEADER', 2048);
define('CL_TABLE_CELL', 4096);
define('CL_XPARAM_A', 8192);
define('CL_XPARAM_B', 16384);


define('CG_BOLD', 1);
define('CG_ITALIC', 2);
define('CG_LIST', 4);
define('CG_NFO', 8);
define('CG_TABLE', 16);

define ('CLS_UL', 1);
define ('CLS_OL', 2);

class wiki_ParserLibrary
{
	private $ctk;
	private $nct;

	private $stk;
	private $nst;

	private $inc_header;
	private $inc_list;

	private $lflags;
	private $gflags;

	private $internal;

	private $list_stack;
	private $fbuf;

	private $len;
	private $pos;

	private $att;

	public function parse(&$body, $internal, $attachments = null)
	{
		$this->fbuf = $this->stk = $this->ctk = "";
		$this->internal = $internal;
		$this->pos = $this->inc_header = $this->inc_list = $this->nct = $this->nst = 0;
		$this->list_stack = array();
		$this->lflags = CL_CTL;
		$this->gflags = 0;
		$pch = null;
		$this->att = $attachments;
		ob_start();

		echo "<p>";
		$ct = null;
		$len = 0;

		while(1) {
			if(!isset($body[$this->pos]))
				break;

			$ch = $body[$this->pos++]; // move on

			if(($this->gflags & CG_NFO) && ($ch != '}' && $ch != "\n")) {
				if($ch == ' ' && $this->nct > 0) {
					$this->parseCtl();
				}
					
				echo $ch;
				continue;
			}

			switch($ch) {
			case "\r":
			break;
			case "\n":
				$this->x0aCtl();
			break;

			case '[':
			case ']':
			case '{':
			case '}':
			case '=':
			case '*':
			case '/':
			case '#':
			case '\\':
			case '|':
			case '-':
				if($ch == '/' && ($this->lflags & CL_AHREF)) {
					$this->stk .= $ch;
					$this->nst++;
					break;
				}

				if(($this->lflags & CL_TABLE) && !($this->lflags & CL_TABLE_CELL)  && $ch != '|') {
					if(!($this->lflags & CL_TABLE_HEADER))
						echo "\t<td>";
					$this->lflags ^= CL_TABLE_CELL;
				}

				if($ch != $pch) {
					$this->parseCtl();
					if(($this->gflags & CG_NFO) && $ch != '}') {
						echo $ch;
						continue;
					}
				}

				if($this->nst > 0 && !($this->lflags & (CL_AHREF|CL_ATITLE|CL_ISRC|CL_XPARAM_A))) {
					echo $this->stk;
					$this->stk = "";
					$this->nst = 0;
				}

				$this->ctk .= $ch;
				$this->nct++;
				$this->len++;
			break;

			default:
				if($this->nct)
					$this->parseCtl();
				
				if($this->lflags & CL_TABLE && !($this->lflags & CL_TABLE_CELL)) {
					if(!($this->lflags & CL_TABLE_HEADER))
						echo "\t<td>";
					$this->lflags ^= CL_TABLE_CELL;
				}

				if(!($this->lflags & (CL_CTL|CL_STR))
				|| ( ($this->lflags&CL_CTL) && !($this->lflags&CL_STR) )) {

					if($this->lflags & CL_CTL)
						$this->lflags ^= CL_CTL;

					$this->lflags ^= CL_STR;
				}

				if( ($this->lflags & CL_OPEN_HEADER) ) {
					$this->lflags ^= CL_OPEN_HEADER;
					$this->lflags ^= CL_HEADER;
				}

				if($ch == '<')
					$ch = "&lt;";
				else
				if($ch == ">")
					$ch = "&gt;";
	
				$this->stk .= $ch;
				$this->nst++;
				$this->len++;
			break;

			}
			$pch = $ch;
		}
		if($this->nst && !($this->lflags & (CL_AHREF|CL_ATITLE)))
			echo $this->stk;

		if($this->nct) {
			$this->lflags ^= CL_TRAIL;
			$this->parseCtl();
		}
		if($this->gflags & CG_LIST) {
			$this->lsPop(0);
			$this->gflags ^= CG_LIST;
		}

		if($this->gflags & CG_TABLE)
				echo "</table><p>\n";

		echo "</p>";
		return ob_get_clean();
	}

	private function parseCtl()
	{
		if(!$this->nct)
			return;


		switch($this->ctk[0]) {
		case '=':
			$this->x76Ctl();
		break;

		case '/':
			$this->x2fCtl();
		break;

		case '\\':
			$this->x5cCtl();
		break;

		case '*':
			$this->x2aCtl();
		break;
		
		case '#':
			$this->x23Ctl();
		break;

		case '[':
			$this->x5bCtl();
		break;

		case ']':
			$this->x5dCtl();
		break;

		case '|':
			$this->x7cCtl();
		break;
		
		case '-':
			$this->x2dCtl();
		break;

		case '{':
		case '}':
			$this->x7bdCtl();
		break;
		}

		$this->nct = 0;
		$this->ctk = "";
	}

	/* \n new line */
	private function x0aCtl()
	{

		if($this->len == 0) {
			if($this->gflags & CG_BOLD) {
				$this->gflags ^= CG_BOLD;
				echo "</strong>";
			}

			if($this->gflags & CG_ITALIC) {
				$this->gflags ^= CG_ITALIC;
				echo "</em>";
			}

			if($this->gflags & CG_LIST) {
				$this->gflags ^= CG_LIST;
				$this->lsPop(0);
			}

			if($this->gflags & CG_TABLE) {
				echo "</table><p>\n";
				$this->gflags ^= CG_TABLE;
			}

			echo "</p><p>";

		} else {
			if($this->nst && !($this->lflags & (CL_AHREF|CL_ATITLE))) {
				echo $this->stk;
				$this->nst = 0;
				$this->stk = "";
			}

			if($this->nct) {
				$this->lflags ^= CL_TRAIL;
				$this->parseCtl();
			}
			if($this->lflags & CL_TABLE) {
				if($this->lflags & CL_TABLE_HEADER)
					echo "</th>\n</tr>\n";
				else
					echo "</td>\n</tr>\n";

			}
			if($this->gflags & CG_NFO)
				echo "\n";

			if( ($this->lflags & CL_HEADER) ) {
				echo "</h{$this->inc_header}><p>";
				$this->inc_header = 0;
			}

		}

		$this->lflags = CL_CTL;
		$this->len = 0;
	}

	// handle =
	private function x76Ctl()
	{
		if(($this->lflags & CL_TABLE) && !($this->lflags & CL_TABLE_HEADER)) {
			$this->lflags ^= CL_TABLE_HEADER;
			echo "\t<th>";
			return;
		}

		if($this->lflags == CL_CTL) {
			$this->inc_header = $this->nct;
			echo "</p><h{$this->inc_header}>";
			$this->lflags ^= CL_OPEN_HEADER;
			return;
		}

		if( ($this->lflags&CL_HEADER) && ($this->lflags & CL_STR) && ($this->lflags & CL_TRAIL) )
			return;


		echo $this->ctk;
	}

	// handle /
	private function x2fCtl()
	{
		if( ($this->lflags & CL_OPEN_HEADER) || ($this->lflags & CL_HEADER) ) {
			echo $this->ctk;
			return;
		}

		if( ($this->lflags & (CL_STR|CL_CTL)) ) {
			$f = 0;
			while($this->nct-- > 0) {
				if(++$f == 2) {
					if($this->gflags & CG_ITALIC)
						echo "</em>";
					else
						echo "<em>";

					$this->gflags ^= CG_ITALIC;
					$f = 0;
				}
			}

			if($f)
				echo "/";
		}
	}

	// handle \
	private function x5cCtl()
	{
		if( ($this->lflags & CL_OPEN_HEADER) || ($this->lflags & CL_HEADER) ) {
			echo $this->ctk;
			return;
		}

		if( ($this->lflags & (CL_STR|CL_CTL)) ) {
			$f = 0;
			while($this->nct-- > 0) {
				if(++$f == 2) {
					echo "<br />";

					$f = 0;
				}
			}

			if($f)
				echo "\\";
		}
	}

	// handle *
	private function x2aCtl()
	{
		if( ( ($this->lflags & CL_STR) || !($this->lflags&(CL_STR|CL_CTL)) )
		|| ( !($this->gflags & CG_LIST) && ($this->lflags & CL_CTL) && $this->nct > 1) ) {
			$f = 0;
			while($this->nct-- > 0) {
				if(++$f == 2) {
					if($this->gflags & CG_BOLD)
						echo "</strong>";
					else
						echo "<strong>";

					$this->gflags ^= CG_BOLD;
					$f = 0;
				}
			}

			if($f)
				echo "*";
		} else
		if(($this->gflags & CG_LIST) && ($this->lflags & CL_CTL) && !($this->lflags & CL_LIST)) {
			if($this->inc_list < $this->nct)
				$this->lsPush(CLS_UL);
			else
			if($this->inc_list > $this->nct)
				$this->lsPop(CLS_UL);
			else
				echo "</li>";

			$this->lflags ^= CL_LIST;
			echo "<li>";
		} else
		if(!($this->gflags & CG_LIST) && ($this->lflags & CL_CTL) && $this->nct == 1) {
			echo "</p>";
			$this->gflags ^= CG_LIST;
			$this->lsPush(CLS_UL);
			echo "<li>";
			$this->lflags ^= CL_LIST;
		}
	}

	// handle #
	private function x23Ctl()
	{
		if(($this->gflags & CG_LIST) && ($this->lflags & CL_CTL) && !($this->lflags & CL_LIST)) {
			if($this->inc_list < $this->nct)
				$this->lsPush(CLS_OL);
			else
			if($this->inc_list > $this->nct)
				$this->lsPop(CLS_OL);
			else
				echo "</li>";

			$this->lflags ^= CL_LIST;
			echo "<li>";
		} else
		if(!($this->gflags & CG_LIST) && ($this->lflags & CL_CTL) && $this->nct == 1) {
			$this->gflags ^= CG_LIST;
			$this->lsPush(CLS_OL);
			echo "<li>";
			$this->lflags ^= CL_LIST;
		}
	}

	// handle [
	private function x5bCtl()
	{
		if($this->nct > 1 && !($this->lflags & (CL_AHREF | CL_ATITLE))) {
			$f = $this->nct - 2;
			echo "<a href=\"";

			while($f-- > 0)
				echo "[";

			$this->lflags ^= CL_AHREF;
			return;
		}

		echo $this->ctk;
	}

	// handle ]
	private function x5dCtl()
	{
		if($this->nct > 1) {
			$f = $this->nct-2;
			if(($this->lflags & (CL_AHREF))) {
				echo $this->internal.$this->stk."\">".$this->stk."</a>";
				$this->nst = 0;
				$this->stk = "";
				$this->lflags ^= CL_AHREF;
			} else
			if(($this->lflags & (CL_ATITLE))) {
				echo $this->stk."</a>";
				$this->nst = 0;
				$this->stk = "";
				$this->lflags ^= CL_ATITLE;
			
			}

			while($f-- > 0)
				echo "]";

			return;
		}

		echo $this->ctk;
	}

	// handle |
	private function x7cCtl()
	{
		if($this->lflags & CL_CTL) {

			if(!($this->gflags & CG_TABLE)) {
				$this->gflags ^= CG_TABLE;
				echo "</p><table>\n";
			}

			if(!($this->lflags & CL_TABLE)) {
				$this->lflags ^= CL_TABLE;
				echo "<tr>\n";
			}
			return;
		}

		if(($this->lflags & CL_TABLE) && !($this->lflags & (CL_AHREF|CL_ISRC|CL_IALT|CL_XPARAM_A|CL_XPARAM_B))) {
			if(!($this->lflags & CL_TRAIL)) {

				if(!($this->lflags & CL_TABLE_HEADER)) {
					echo "</td>\n";
					$this->lflags ^= CL_TABLE_CELL;
				}
				else {
					echo "</th>\n";
					$this->lflags ^= (CL_TABLE_HEADER|CL_TABLE_CELL);
				}
			}

			return;
		}

		if(!($this->lflags&(CL_AHREF|CL_ISRC|CL_IALT|CL_XPARAM_A|CL_XPARAM_B))) {
			echo $this->ctk;
			return;
		}

		if($this->lflags & CL_ISRC) {
			// attachments addition 2014/07/25
			if(is_numeric($this->stk) && $this->att) {
				foreach($this->att as $a)
					if($a['id'] == $this->stk)
						$this->stk = $a['url'];
			}

			echo "{$this->stk}\" alt=\"";
			$this->lflags ^= (CL_ISRC|CL_IALT);
			$this->stk = "";
			$this->nst = 0;
			return;
		}

		if(($this->lflags & CL_IALT)) {
			echo "{$this->stk}\" style=\"";
			$this->lflags ^= (CL_IALT|CL_XPARAM_A);
			$this->stk = "";
			$this->nst = 0;
			return;
		}

		$this->lflags ^= CL_AHREF;
		$this->lflags ^= CL_ATITLE;

		if($this->nct) {
			if($this->nst < 8 || !$this->checkUrl($this->stk)) {
				echo $this->internal.$this->stk."\">";
				$this->nst = 0;
				$this->stk = "";
			} else {
				echo $this->stk."\">";
				$this->stk = "";
				$this->nst = 0;
			}
		}

	}

	private function x7bdCtl()
	{
		if($this->nct == 2 && !($this->gflags & CG_NFO))
			$this->parseImg();
		else
		if($this->nct == 3)
			$this->parseNoWiki();
		else
			echo $this->ctk;
	}

	private function parseImg()
	{
		switch($this->ctk[0]) {

		case '{':
			if($this->lflags & CL_IALT) {
				echo $this->ctk;
				return;
			}

			$this->lflags ^= CL_ISRC;
			echo "<img src=\"";
			break;

		case '}':
			if($this->stk) {
				echo $this->stk;
				$this->stk = "";
			}

			if($this->lflags & CL_IALT)
				$this->lflags ^= CL_IALT;
			else
			if($this->lflags & CL_ISRC)
				$this->lflags ^= CL_ISRC;
			else
			if($this->lflags & CL_XPARAM_A)
				$this->lflags ^=CL_XPARAM_A;
			else {
				echo $this->ctk;
				return;
			}

			echo "\" />";
			break;
		}
	}

	private function parseNoWiki()
	{
		switch($this->ctk[0]) {

		case '{':
			if($this->lflags & CL_CTL) {
				$this->gflags ^= CG_NFO;
				echo "<pre>";
			}
			else
			if($this->lflags & CL_STR) {
				$this->gflags ^= CG_NFO;
				echo "<samp>";
			}
			break;

		case '}':
			$this->gflags ^= CG_NFO;
			if($this->lflags & CL_STR)
				echo "</samp>";
			else
				echo "</pre>";

			break;
		}
	}

	private function x2dCtl()
	{
		if(($this->lflags&(CL_XPARAM_A|CL_AHREF))) {
			$this->stk .= $this->ctk;
			$this->ctk = "";
			return;
		}

		if($this->nct < 4) {
			echo $this->ctk;
			return;
		}

		if(($this->lflags&CL_CTL) && ($this->lflags&CL_TRAIL))
			echo "</p><hr /><p>";
		else
			echo $this->ctk;
	}

	private function checkUrl($check)
	{
		$url = "http://";
		for($i = 0; $i < 7; $i++)
			if($url[$i] != $check[$i])
				return false;

		return true;
		
	}

	private function lsPush($t)
	{
		$this->inc_list++;
		if($t == CLS_UL)
			echo "<ul>";
		else
		if($t == CLS_OL)
			echo "<ol>";

		array_push($this->list_stack, $t);
		$this->lsn++;
	}

	private function lsPop($t)
	{
		while($this->inc_list > $this->nct) {
			$this->lsn--;
			if($this->list_stack[$this->lsn] == CLS_UL)
				echo "</li></ul>";
			else
			if($this->list_stack[$this->lsn] == CLS_OL)
				echo "</li></ol>";
			array_pop($this->list_stack);

			$this->inc_list--;
		}

		if($this->inc_list > 0) {
			echo "</li>";
		} else
			echo "<p>";

	}

}
?>
