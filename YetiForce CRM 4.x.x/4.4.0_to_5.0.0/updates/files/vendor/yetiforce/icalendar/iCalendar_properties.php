<?php

// $Id: iCalendar_properties.php,v 1.13 2005/07/21 22:42:13 defacer Exp $
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyAction.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyAttach.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyAttendee.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyCalscale.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyCategories.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyClass.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyComment.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyCompleted.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyContact.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyCreated.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDaylightc.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDescription.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDtend.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDtstamp.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDtstart.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDue.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyDuration.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyExdate.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyExrule.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyFreebusy.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyGeo.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyLastmodified.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyLocation.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyMethod.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyOrganizer.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyPercentcomplete.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyPriority.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyProdid.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyRdate.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyRecurrenceid.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyRelatedto.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyRequeststatus.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyResources.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyRrule.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertySequence.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyStandardc.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyStatus.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertySummary.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyTransp.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyTrigger.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyTzid.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyTzoffsetto.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyUid.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyUrl.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyVersion.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyX.php');
Vtiger_Loader::includeOnce('~vendor/yetiforce/icalendar/IcalendarPropertyXwralarmuid.php');

class IcalendarProperty
{
	// Properties can have parameters, but cannot have other properties or components

	public $parent_component;
	public $value;
	public $parameters;
	public $valid_parameters;
	// These are common for 95% of properties, so define them here and override as necessary
	public $val_multi = false;
	public $val_default;

	public function __construct()
	{
		$this->construct();
	}

	public function construct()
	{
		$this->parameters = [];
	}

	// If some property needs extra care with its parameters, override this
	// IMPORTANT: the parameter name MUST BE CAPITALIZED!
	public function isValidParameter($parameter, $value)
	{
		if (is_array($value)) {
			if (!IcalendarParameter::multipleValuesAllowed($parameter)) {
				return false;
			}
			foreach ($value as $item) {
				if (!IcalendarParameter::isValidValue($this, $parameter, $item)) {
					return false;
				}
			}

			return true;
		}

		return IcalendarParameter::isValidValue($this, $parameter, $value);
	}

	public function invariantHolds()
	{
		return true;
	}

	// If some property is very picky about its values, it should do the work itself
	// Only data type validation is done here
	public function isValidValue($value)
	{
		if (is_array($value)) {
			if (!$this->val_multi) {
				return false;
			} else {
				foreach ($value as $oneval) {
					if (!\ICalendarRfc::rfc2445IsValidValue($oneval, $this->val_type)) {
						return false;
					}
				}
			}

			return true;
		}

		return \ICalendarRfc::rfc2445IsValidValue($value, $this->val_type);
	}

	public function defaultValueICal()
	{
		return $this->val_default;
	}

	public function setParentComponent($componentname)
	{
		if (class_exists('Icalendar' . ucfirst(strtolower(substr($componentname, 1))))) {
			$this->parent_component = strtoupper($componentname);

			return true;
		}

		return false;
	}

	public function setValueICal($value)
	{
		if ($this->isValidValue($value)) {
			// This transparently formats any value type according to the iCalendar specs
			if (is_array($value)) {
				foreach ($value as $key => $item) {
					$value[$key] = \ICalendarRfc::rfc2445DoValueFormatting($item, $this->val_type);
				}
				$this->value = implode(',', $value);
			} else {
				$this->value = \ICalendarRfc::rfc2445DoValueFormatting($value, $this->val_type);
			}

			return true;
		}

		return false;
	}

	public function setParameterICal($name, $value)
	{
		// Uppercase
		$name = strtoupper($name);

		// Are we trying to add a valid parameter?
		if (!isset($this->valid_parameters[$name])) {
			// If not, is it an x-name as per RFC 2445?
			if (!\ICalendarRfc::rfc2445IsXname($name)) {
				return false;
			}
			// No more checks -- all components are supposed to allow x-name parameters
		}

		if (!$this->isValidParameter($name, $value)) {
			return false;
		}

		if (is_array($value)) {
			foreach ($value as $key => $element) {
				$value[$key] = IcalendarParameter::doValueFormatting($name, $element);
			}
		} else {
			$value = IcalendarParameter::doValueFormatting($name, $value);
		}

		$this->parameters[$name] = $value;

		// Special case: if we just changed the VALUE parameter, reflect this
		// in the object's status so that it only accepts correct type values
		if ($name == 'VALUE') {
			$this->val_type = constant('RFC2445_TYPE_' . str_replace('-', '_', $value));
		}

		return true;
	}

	public function getParameterICal($name)
	{
		// Uppercase
		$name = strtoupper($name);

		if (isset($this->parameters[$name])) {
			// If there are any double quotes in the value, invisibly strip them
			if (is_array($this->parameters[$name])) {
				foreach ($this->parameters[$name] as $key => $value) {
					if (substr($value, 0, 1) == '"') {
						$this->parameters[$name][$key] = substr($value, 1, strlen($value) - 2);
					}
				}

				return $this->parameters[$name];
			} else {
				if (substr($this->parameters[$name], 0, 1) == '"') {
					return substr($this->parameters[$name], 1, strlen($this->parameters[$name]) - 2);
				}
			}
		}

		return null;
	}

	public function serialize()
	{
		$string = $this->name;

		if (!empty($this->parameters)) {
			foreach ($this->parameters as $name => $value) {
				$string .= ';' . $name . '=';
				if (is_array($value)) {
					$string .= implode(',', $value);
				} else {
					$string .= $value;
				}
			}
		}

		$string .= ':' . $this->value;

		return \ICalendarRfc::rfc2445Fold($string) . RFC2445_CRLF;
	}
}
