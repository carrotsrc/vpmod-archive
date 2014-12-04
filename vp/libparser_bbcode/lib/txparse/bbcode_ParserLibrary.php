<?php
/* (C)opyright 2014, Zunautica Initiatives Ltd.
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 
 
 * This simple bbcode parser state machine.
 * It has three main modes: normal, buffer and tag space.
 * 
 * Normal Mode - it will either print characters or look for '['
 * When it hits the '[' character, this toggle Tag Space.
 * 
 * Tag Space - it will buffer characters until it finds a delimiter 
 * which will describe what the previously buffered characters are. It 
 * will continue this process until it reachs the ']' which will trigger 
 * it to parse all the collected data as a tag.
 *
 * Buffer Mode - If an element requires it's contents, the opening tag
 * will place the machine in Buffer mode, which acts like normal mode
 * but buffers the characters instead of echoing them. The closing tag
 * will then parse all the collected information into markup
 *
 * Current drawbacks -
 * - It will slip up in Buffered Mode if it encounters any bbcode
 *   that is not the closing tag of the element that switched on
 *   buffered mode.
 *
 * - No management - if there's an element left open it will remain open
 *   for the rest of the document
 */
	define('BB_NORMAL', 0);
	define('BB_TAG', 1);
	define('BB_CLOSE_TAG', 2);
	
	define('BB_ATTR_NAME', 4);
	define('BB_ATTR_VALUE', 8);
	define('BB_ATTR_FLIP', 12);
	
	define('BB_ATTR_LIST', 16);

	define('BB_STRING', 32);

	define('BB_BUFFER', 256);

	define('BBE_NONE', 0);
	define('BBE_TABLE', 1);
	define('BBE_TABLE_ROW', 2);
	define('BBE_LIST', 4);
	define('BBE_LI', 8);

	class bbcode_ParserLibrary
	{
		private $mode;
		private $element;
		private $tokens;
		private $tag;
		private $attributes;
		private $attr;
		private $buffer;
		public function parse(&$text, $host)
		{

			$this->mode = BB_NORMAL;
			$this->attributes = array();
			$this->tokens = null;
			$pr = null;

			ob_start();
			for($i = 0; isset($text[$i]); $i++) {
				$ch = $text[$i];
				if($this->mode == BB_NORMAL) {
					/* your normal every day character */
					if($ch == "\n") {
						if($pr == $ch)
							echo "<br />";
						else
						if($this->element & BBE_LI) {
							echo "</li>";
							$this->element ^= BBE_LI;
						}
					}
					else
					if($ch == '[')
						$this->mode = BB_TAG; // Enter tag space
					else
					if($ch == '\\') // this is the escape character
						echo $text[++$i];
					else
						echo $ch;
					if($ch != "\r")
						$pr = $ch;
				}
				else
				if($this->mode == BB_BUFFER) {
					// we're in buffer mode
					if($ch == '[')
						$this->mode ^= BB_TAG; // enter tag space
					else
					if($ch == '\\')
						$this->buffer .= $text[++$i];
					else
						$this->buffer .= $ch;
				}
				else 
				if($this->mode & BB_TAG) {
					// need to parse character in tag space
					$this->parseToken($ch);
				}
			}

			return ob_get_clean();
		}

		private function parseToken($ch)
		{
			/* we're in tag space if we're here.
			*  that means we've already encountred a square
			*  opening brack
			*/
			switch($ch) {
			case ' ':
				if($this->mode & BB_STRING)
					$this->tokens .= $ch;
				else
				if(!($this->mode & BB_ATTR_LIST)) {
					$this->mode ^= (BB_ATTR_LIST|BB_ATTR_NAME);
					$this->tag = $this->tokens;
					$this->tokens = null;
				}
				else
				if($this->mode & BB_ATTR_VALUE) {
					if($this->attr == null)
						$this->attr = array('a' => null);

					$this->attr['v'] = $this->tokens;
					$this->tokens = "";
					$this->attributes[$this->attr['a']] = $this->attr['v'];
					$this->attr = null;
					$this->mode ^= BB_ATTR_FLIP;
				}
				break;
			case '/':
				if($this->mode == BB_TAG
				||($this->mode == (BB_TAG|BB_BUFFER)))
					$this->mode ^= BB_CLOSE_TAG;
				else
				if(($this->mode & BB_STRING) || ($this->mode & BB_ATTR_VALUE))
					$this->tokens .= $ch;

				break;

			case ']':
				if($this->mode & BB_ATTR_VALUE) {
					if($this->attr == null)
						$this->attr = array('a' => 'simple');

					$this->attr['v'] = $this->tokens;
					$this->tokens = "";
					$this->attributes[$this->attr['a']] = $this->attr['v'];
					$this->attr = null;
					$this->mode ^= BB_ATTR_VALUE;
				}
				else {
					$this->tag = $this->tokens;
					$this->tokens = null;
				}

				$this->parseTag();
				if($this->mode & BB_BUFFER)
					$this->mode = BB_BUFFER;
				else
					$this->mode = BB_NORMAL;
			break;

			case '=':
				if($this->mode == BB_TAG) {
					if(isset($this->tokens[0])) {
						$this->tag = $this->tokens;
						$this->tokens = null;
					}
					$this->mode ^= BB_ATTR_VALUE;
				}
				else
				if($this->mode & BB_ATTR_NAME) {
					$this->attr = array('a' => $this->tokens, 'v' => null);
					$this->tokens = null;
					$this->mode ^= BB_ATTR_FLIP;
				}
				break;

			case '"':
				$this->mode ^= BB_STRING;
				break;

			default:
				$this->tokens .= $ch;
				break;
			}
		}

		private function parseTag()
		{
			/* if we're here is means we have got all the information
			*  for a tag, whether it is an opener or closer.
			*/
			$style = null;
			switch($this->tag) {
			case 'b':
			case 'u':
			case 's':
			case 'i':
			case 'center':
			case 'code':
				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				echo $this->tag.">";
				break;

			case 'tr':
				if(!($this->element & (BBE_TABLE|BBE_TABLE_ROW)))
					break;

				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				$this->element ^= BBE_TABLE_ROW;
				echo $this->tag.">";
				break;

			case 'th':
			case 'td':
				if(!($this->element & BBE_TABLE)
				|| !($this->element & BBE_TABLE_ROW))
					break;

				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				echo $this->tag.">";
				break;

			case 'table':
				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				echo $this->tag.">";
				$this->element ^= BBE_TABLE;
				break;

			case 'ul':
			case 'ol':
				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				echo $this->tag.">";
				$this->element ^= BBE_LIST;
				break;

			case '*':
				$this->element ^= BBE_LI;
				$this->tag = "li";
			case 'li':
				if(!($this->element & BBE_LIST))
					break;
				echo "<";
				if($this->mode & BB_CLOSE_TAG)
					echo "/";

				echo $this->tag.">";
				break;

			case 'quote':

				if($this->mode & BB_CLOSE_TAG)
					echo '</blockquote>';
				else
				if(isset($this->attributes['simple']))
					echo "<blockquote cite=\"{$this->attributes['simple']}\">";
				else
					echo '<blockquote>';
				break;

			case 'fontsize':
				$style = 'font-size';
			case 'fontcolor':
				if($this->mode & BB_CLOSE_TAG)
					echo '</span>';
				else {
					if(!isset($style))
						$style = 'color';

					if(isset($this->attributes['simple']))
						echo "<span style=\"{$style}: {$this->attributes['simple']};\">";
					else
						echo '<span>';
				}
				break;

			case 'img':
				if($this->mode & BB_CLOSE_TAG) {
					echo "<img src=\"{$this->buffer}\"";
					if(sizeof($this->attributes) > 0) {
						if(isset($this->attributes['simple'])) {
							$at = explode("x", $this->attributes['simple']);
							if(isset($at[1]))
								echo " style=\"width: {$at[0]}px; height: {$at[1]}px;\"";
						}
						else {
							echo " style=\"";
							if(isset($this->attributes['width']))
								echo "width: {$this->attributes['width']}px;";

							if(isset($this->attributes['height']))
								echo "height: {$this->attributes['height']}px;";
							echo "\"";
						}
					}
					echo " />";
					$this->mode ^= BB_BUFFER;
					$this->buffer = "";
				}
				else 
					$this->mode = BB_BUFFER;
				break;

			case 'url':
				if($this->mode & BB_CLOSE_TAG) {
					if($this->mode & BB_BUFFER) {
						echo "<a href=\"";
						if(isset($this->attributes['simple'])) {
							echo $this->attributes['simple'];
							echo "\">{$this->buffer}</a>";
						}
						else {
							echo $this->buffer;
							echo "\">{$this->buffer}</a>";
						}

						$this->buffer = "";
						$this->mode ^= BB_BUFFER;
					}
				}
				else
					$this->mode = BB_BUFFER;
				break;
			}

			$this->tokens = null;
			if(!($this->mode & BB_BUFFER))
				$this->attributes = array();
			$this->tag = null;
		}
	}
?>
