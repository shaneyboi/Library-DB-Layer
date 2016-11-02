<?php
	
	/**
	* This class produces the regex string for getting Call Numbers(with max length 2).
	*/
	class CallNumber 
	{

		private $alphabet = array("A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		private $combinations = array("", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T", "U", "V", "W", "X", "Y", "Z");
		protected $callNumberRange = array();

		/**
		 * @param - string - starting regex string given by the user.
		 * @return - string - compiled regex string for use with DB.
		 **/
		public function process($regex){
			if ($regex[0] == '/') {
				return substr($regex, 1, strlen($regex)-1);
			}
			$regex = preg_replace('/\s+/', '', strtoupper($regex));
			$regex = $this->replaceDash($regex);
			$regex = $this->replaceStar($regex);
			$regex = $this->replacePlus($regex);
			$regex = $this->replaceComma($regex);
			$regex = "^(".$regex.")[0-9].*";
			return $regex;
		}

		public function callNumberProduced(){
			if (!$this->callNumberRange) { return false; }
			return true;
		}// end callNumberProduced()

		/**
		 * @param - string - regex created by user (not in DB form).
		 * @return - string - changes all Comma's into (OR) "|".
		 **/
		protected function replaceComma($regex){
			if(strpos($regex, ',') !== false){ $regex = preg_replace('/\,/', '|', $regex); }
			return $regex;
		}//end replaceComma()

		/**
		 * @param - string - regex created by user (not in DB form).
		 * @return - string - changes all Asterisk's into (ANY) "[A-Z]?".
		 **/
		protected function replaceStar($regex){
			if(strpos($regex, '*') !== false){ $regex = preg_replace('/\*/', '[A-Z]?', $regex); }
			return $regex;
		}//end replaceStar()

		/**
		 * @param - string - regex created by user (not in DB form).
		 * @return - string - changes all Asterisk's into (exclude singles) "[A-Z]".
		 **/
		protected function replacePlus($regex){
			if(strpos($regex, '+') !== false){ $regex = preg_replace('/\+/', '[A-Z]+', $regex); }
			return $regex;	
		}//end replacePlus()

		/**
		 * @param - string - regex created by user (not in DB form).
		 * @return - string - changes a range of call numbers to correct regex db form.
		 * 		REQUIRED: replaceComma must be call after this function
		 * E.g.
		 *     P-PZ ==> P[A-Z]?
		 *     A-G ==> A[A-Z]?, B[A-Z]?, .... , G
		 *     AD-HI ==> A[D-Z], B[A-Z]?, ... , H[A-I]?
		 **/
		protected function replaceDash($regex){
			$temp = '';
			if (strpos($regex, '-') !== false) {
				preg_match_all( '/([A-Z][A-Z]?)-([A-Z][A-Z]?)/', $regex, $match );
				for ($i=0; $i < count($match[0]); $i++) { 
					$begin = $this->findIndex($match[1][$i]);
					$end = $this->findIndex($match[2][$i]);
					$temp .= $this->produceSimpleRegex($begin, $end);
					if ($i < count($match[0])-1) {
						$temp .= ",";
					}
				}
				return $temp;
			}
			return $regex;
		}//end replaceDash()

		/** 
		 * Produces the Array of All Call Number Abbrevations Possible.
		 **/
		protected function produceCallNumberRange(){
			$this->callNumberRange = array();
			foreach ($this->alphabet as $letter) { 
				foreach ($this->combinations as $key) {
					$this->callNumberRange[] = $letter.$key;
				}
			}
		}// end produceCallNumberRange()

		/** Loop Through Entire Call Number Abbreviations with max length 2.
		 * @param - lookFor - string - Call Number Abbreviation
		 * @param - index - integer - starting index if known(optional)
		 * @return - integer - returns the index of the matched call number Abberviation.
		 */
		protected function loopThroughCallNumber($lookFor){
			$temp = 0;
			if (!$this->callNumberProduced) {
				$this->produceCallNumberRange();
			}

			$temp = $this->loopAlphabet($lookFor);

			if (strlen($lookFor) > 1) {
				//If the string is > than length 1. Check larger array for index.
				for ($i=($temp*26); $i < count($this->callNumberRange); $i++) { 
					if ($lookFor == $callNumberRange[$i]) {
						return $i;
					}
				}
			}
			
			return false;
		}// end loopThroughCallNumber()

		/** Iterates through the private variable alphabet. 
		 * @param - string (Max length: 1) - Call Number String Abbreviation.
		 * @return - returns the index of the matched string.
		 **/
		protected function loopAlphabet($lookFor){
			//Look through Single Alphabet.
			for ($i=0; $i < count($this->alphabet); $i++) { 
				if ($lookFor[0] == $this->alphabet[$i]) {
					return $i;
				}
			}
			return false;
		}// end loopAlphabet()

		// Find the Unique ID of Call Number Abbreviation.
		/**
		 *  @param - letter (String w/ max length: 2) - A, AB, AC ...
		 * 	@return - integer - Unique ID of Call Number Abbreviation.
		 *  0=>A, ..., 26=>AZ, 27=>B ....
		 **/
		protected function findIndex($lookFor){
			$temp = 0;

			//Look at the first letter of the Call Number and return the index of it.
			$temp = $this->loopAlphabet($lookFor);

			//If call number is length of two, look at the the second letter and find the index.
			if (strlen($lookFor) > 1) {
				$temp2 = 0;
				$temp2 = $this->loopAlphabet($lookFor[1]);
				return ($temp*27)+($temp2); 
				// The summation of the first letter's index and the second letter's index creates the unique ID of the call number abbreviation.
			}

			return ($temp*27);
			//if it is a single letter index return without summing anything.
		}// end findIndex()

		/** Produces a simple CSV to produce everything in between the given index.
		 * @param - start - integer - Unique Call Number ID and starting iteration.
		 * @param - end - integer - Unique Call Number ID and last iteration. 
		 * @return - string - returns a CSV type style for call number.
		 **/
		protected function produceSimpleRegex($start, $end){
			$startRemainder = $start % 27;
			$startQuotient = ($start - $startRemainder) / 27;
			$endRemainder = $end % 27;
			$endQuotient = ($end - $endRemainder) / 27;


			$string = '';
			for ($i=$startQuotient; $i <= $endQuotient; $i++) { 
				$string .= $this->alphabet[$i];
				if ($i == $endQuotient) {
					if ($endRemainder > 0) {
						$string .= "[A-".$this->alphabet[($endRemainder)]."]?";
					}
				} else if ($i == $startQuotient) {
					if ($startRemainder > 0){
						$string .= "[".$this->alphabet[($startRemainder)]."-Z],";
					} else {
						$string .="[A-Z]?,";
					}
				} else {
					$string .= "[A-Z]?,";
				}
			}

			return $string;
		}// end produceSimpleRegex()
	}// end class.
?>