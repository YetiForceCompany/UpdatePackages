<?php

namespace libphonenumber;

/**
 * Interface MatcherAPIInterface.
 *
 * Internal phonenumber matching API used to isolate the underlying implementation of the
 * matcher and allow different implementations to be swapped in easily.
 *
 * @internal
 */
interface MatcherAPIInterface
{
	/**
	 * Returns whether the given national number (a string containing only decimal digits) matches
	 * the national number pattern defined in the given {@code PhoneNumberDesc} message.
	 *
	 * @param string          $number
	 * @param PhoneNumberDesc $numberDesc
	 * @param bool            $allowPrefixMatch
	 *
	 * @return bool
	 */
	public function matchNationalNumber($number, PhoneNumberDesc $numberDesc, $allowPrefixMatch);
}
