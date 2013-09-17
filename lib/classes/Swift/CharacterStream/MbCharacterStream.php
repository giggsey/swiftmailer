<?php
 
/**
 * A CharacterStream implementation which skips over all the manual processing
 *  performed by NgCharacterStream in favour of using the mb_ extension.
 * 
 * @package Swift
 * @author mappu
 */
class Swift_CharacterStream_MbCharacterStream implements Swift_CharacterStream {
  
	private $charset = null;
	private $strpos  = 0;
	private $buffer  = '';
	private $strlen  = 0;
	
	public function flushContents() {
		$this->strpos = 0;
		$this->buffer = '';
	}
 
	public function importByteStream(\Swift_OutputByteStream $os) {
		$this->flushContents();
		$blocks = 512;
		$os->setReadPointer(0);
		while( ($read = $os->read($blocks)) !== false ) {
			$this->write( $read );
		}
	}
 
	public function importString($string) {
		$this->flushContents();
		$this->write($string);
	}
 
	public function read($length) {
		if ($this->strpos >= $this->strlen) {
			return false;
		}
		
		$readChars = min($length, $this->strlen - $this->strpos);
		
		$ret = mb_substr($this->buffer, $this->strpos, $readChars, $this->charset);
		
		$this->strpos += $readChars;
		
		return $ret;
	}
 
	/**
	 * 
	 * @param type $length
	 * @return int[]
	 */
	public function readBytes($length) {
		$read = $this->read($length);
		
		if ($read !== false) {
			return array_map('ord', str_split($read, 1));
			
		} else {
			return false;
		}
	}
 
	public function setCharacterReaderFactory(\Swift_CharacterReaderFactory $factory) {
		// Ignore
	}
 
	public function setCharacterSet($charset) {
		$this->charset = $charset;
	}
 
	public function setPointer($charOffset) {
		$this->strpos = $charOffset;
	}
 
	public function write($chars) {
		$this->buffer .= $chars;
		$this->strlen += mb_strlen($chars, $this->charset);
	}
}
