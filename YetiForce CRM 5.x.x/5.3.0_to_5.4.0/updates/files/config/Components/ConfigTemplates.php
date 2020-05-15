<?php
/**
 * Components config.
 *
 * @copyright YetiForce Sp. z o.o
 * @license   YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 */
return [
	'AddressFinder' => [
		'REMAPPING_OPENCAGE' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'The main function to remapping fields for OpenCage. It should be a function.'
		],
		'REMAPPING_OPENCAGE_FOR_COUNTRY' => [
			'type' => 'function',
			'default' => "return [
		'Australia' => function (\$row) {
			return [
				'addresslevel1' => [\$row['components']['country'] ?? '', \$row['components']['ISO_3166-1_alpha-2'] ?? ''],
				'addresslevel2' => \$row['components']['state'] ?? '',
				'addresslevel3' => \$row['components']['state_district'] ?? '',
				'addresslevel4' => \$row['components']['county'] ?? '',
				'addresslevel5' => \$row['components']['suburb'] ?? \$row['components']['neighbourhood'] ?? \$row['components']['city_district'] ?? '',
				'addresslevel6' => \$row['components']['city'] ?? \$row['components']['town'] ?? \$row['components']['village'] ?? '',
				'addresslevel7' => \$row['components']['postcode'] ?? '',
				'addresslevel8' => \$row['components']['road'] ?? '',
				'buildingnumber' => \$row['components']['house_number'] ?? '',
				'localnumber' => \$row['components']['local_number'] ?? '',
			];
		},
	];",
			'description' => 'Function to remapping fields in countries for OpenCage. It should be function.'
		],
		'nominatimMapUrlCustomOptions' => [
			'default' => [],
			'description' => "Additional headers for connections with NominatimGeocoder API e.g. \n['auth' => ['username', 'password']]\n['auth' => ['username', 'password', 'digest']]\n['headers' => 'X-KAY' => 'key-x']"
		],
		'nominatimRemapping' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'Main function to remapping fields for NominatimGeocoder. It should be function.'
		],
		'nominatimRemappingForCountry' => [
			'type' => 'function',
			'default' => "return [
			'AU' => function (\$row) {
				return [
					'addresslevel1' => [\$row['address']['country'] ?? '', \$row['address']['country_code'] ?? ''],
					'addresslevel2' => \$row['address']['state'] ?? '',
					'addresslevel3' => \$row['address']['state_district'] ?? '',
					'addresslevel4' => \$row['address']['county'] ?? '',
					'addresslevel5' => \$row['address']['suburb'] ?? \$row['address']['neighbourhood'] ?? \$row['address']['city_district'] ?? '',
					'addresslevel6' => \$row['address']['city'] ?? \$row['address']['town'] ?? \$row['address']['village'] ?? '',
					'addresslevel7' => \$row['address']['postcode'] ?? '',
					'addresslevel8' => \$row['address']['road'] ?? '',
					'buildingnumber' => \$row['address']['house_number'] ?? '',
					'localnumber' => \$row['address']['local_number'] ?? '',
				];
			},
		];",
			'description' => 'Function to remapping fields in countries for Nominatim. It should be a function.'
		],
		'yetiForceRemapping' => [
			'type' => 'function',
			'default' => 'return null;',
			'description' => 'Main function to remapping fields for YetiForceGeocoder. It should be a function.'
		],
		'yetiForceRemappingForCountry' => [
			'type' => 'function',
			'default' => "return [
			'AU' => function (\$row) {
				return [
					'addresslevel1' => [\$row['address']['country'] ?? '', \$row['address']['country_code'] ?? ''],
					'addresslevel2' => \$row['address']['state'] ?? '',
					'addresslevel3' => \$row['address']['state_district'] ?? '',
					'addresslevel4' => \$row['address']['county'] ?? '',
					'addresslevel5' => \$row['address']['suburb'] ?? \$row['address']['neighbourhood'] ?? \$row['address']['city_district'] ?? '',
					'addresslevel6' => \$row['address']['city'] ?? \$row['address']['town'] ?? \$row['address']['village'] ?? '',
					'addresslevel7' => \$row['address']['postcode'] ?? '',
					'addresslevel8' => \$row['address']['road'] ?? '',
					'buildingnumber' => \$row['address']['house_number'] ?? '',
					'localnumber' => \$row['address']['local_number'] ?? '',
				];
			},
		];",
			'description' => 'Function to remapping fields in countries for YetiForceGeocoder. It should be a function.'
		],
	],
	'Backup' => [
		'BACKUP_PATH' => [
			'default' => '',
			'description' => 'Backup catalog path.',
			'validation' => function () {
				$arg = func_get_arg(0);
				return '' === $arg || \App\Fields\File::isAllowedDirectory($arg);
			}
		],
		'EXT_TO_SHOW' => [
			'default' => ['7z', 'bz2', 'gz', 'rar', 'tar', 'tar.bz2', 'tar.gz', 'tar.lzma', 'tbz2', 'tgz', 'zip', 'zipx'],
			'description' => 'Allowed extensions to show on the list.',
		]
	],
	'Dav' => [
		'CALDAV_DEFAULT_VISIBILITY_FROM_DAV' => [
			'default' => false,
			'description' => "Default visibility for events synchronized with CalDAV. Available values: false/'Public'/'Private'\nSetting default value will result in  skipping visibility both ways, default value for both ways will be set.",
		],
		'CALDAV_EXCLUSION_FROM_DAV' => [
			'default' => false,
			'description' => "Rules to set exclusions/omissions in synchronization\nExample. All private entries from CalDAV should not be synchronized: ['visibility' => 'Private']",
		],
		'CALDAV_EXCLUSION_TO_DAV' => [
			'default' => false,
			'description' => 'Exclusions',
		]
	],
	'Export' => [
		'BLOCK_NAME' => [
			'default' => true,
			'description' => 'Block names are added to headers',
		]
	],
	'Mail' => [
		'MAILTO_LIMIT' => [
			'default' => 2030,
			'description' => "Recommended configuration\nOutlook = 2030\nThunderbird = 8036\nGMAIL = 8036"
		],
		'RC_COMPOSE_ADDRESS_MODULES' => [
			'default' => ['Accounts', 'Contacts', 'OSSEmployees', 'Leads', 'Vendors', 'Partners', 'Competition'],
			'description' => 'List of modules from which you can choose e-mail address in the mail.'
		],
		'helpdeskCreatedStatus' => [
			'default' => 'Open',
			'description' => 'What status should be set when a ticket is created.'
		],
		'HELPDESK_NEXT_WAIT_FOR_RESPONSE_STATUS' => [
			'default' => 'Answered',
			'description' => 'What status should be set when a new mail is received regarding a ticket, whose status is awaiting response.'
		],
		'HELPDESK_OPENTICKET_STATUS' => [
			'default' => 'Answered',
			'description' => 'What status should be set when a ticket is closed, but a new mail regarding the ticket is received.'
		],
		'MAILER_REQUIRED_ACCEPTATION_BEFORE_SENDING' => [
			'default' => false,
			'description' => 'Required acceptation before sending mails.'
		],
		'defaultRelationModule' => [
			'default' => '',
			'description' => "Default selected relation module in mail bar.\n@var string Module name"
		],
		'autoCompleteFields' => [
			'default' => [
				'Accounts' => ['accountname' => 'subject'],
				'Leads' => ['lastname' => 'fromNameSecondPart', 'company' => 'fromName'],
				'Vendors' => ['vendorname' => 'subject'],
				'Partners' => ['subject' => 'subject'],
				'Competition' => ['subject' => 'subject'],
				'OSSEmployees' => ['name' => 'fromNameFirstPart', 'last_name' => 'fromNameSecondPart'],
				'Contacts' => ['firstname' => 'fromNameFirstPart', 'lastname' => 'fromNameSecondPart'],
				'SSalesProcesses' => ['subject' => 'subject'],
				'Project' => ['projectname' => 'subject'],
				'ServiceContracts' => ['subject' => 'subject'],
				'Campaigns' => ['campaignname' => 'subject'],
				'FBookkeeping' => ['subject' => 'subject'],
				'HelpDesk' => ['ticket_title' => 'subject'],
				'ProjectMilestone' => ['projectmilestonename' => 'subject'],
				'SQuoteEnquiries' => ['subject' => 'subject'],
				'SRequirementsCards' => ['subject' => 'subject'],
				'SCalculations' => ['subject' => 'subject'],
				'SQuotes' => ['subject' => 'subject'],
				'SSingleOrders' => ['subject' => 'subject'],
				'SRecurringOrders' => ['subject' => 'subject'],
				'FInvoice' => ['subject' => 'subject'],
				'SVendorEnquiries' => ['subject' => 'subject'],
				'ProjectTask' => ['projecttaskname' => 'subject'],
				'Services' => ['servicename' => 'subject'],
				'Products' => ['productname' => 'subject']
			],
			'description' => "Default auto-complete data from mail bar.\n@var array Map. Example ['Accounts' => ['accountname' => 'subject']]"
		]
	],
	'YetiForce' => [
		'watchdogUrl' => [
			'default' => '',
			'description' => 'YetiForce watchdog monitor URL',
			'validation' => function () {
				$arg = func_get_arg(0);
				return empty($arg) || \App\Validator::url($arg);
			}
		],
		'domain' => [
			'default' => false,
			'description' => 'CRM system URL',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'phpVersion' => [
			'default' => false,
			'description' => 'PHP version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'crmVersion' => [
			'default' => false,
			'description' => 'CRM version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'dbVersion' => [
			'default' => false,
			'description' => 'Database version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'osVersion' => [
			'default' => false,
			'description' => 'System version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'sapiVersion' => [
			'default' => false,
			'description' => 'API server version',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'lastCronTime' => [
			'default' => false,
			'description' => 'Last Cron time',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'spaceRoot' => [
			'default' => false,
			'description' => 'Root space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'spaceStorage' => [
			'default' => false,
			'description' => 'Storage space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'spaceTemp' => [
			'default' => false,
			'description' => 'Temporary directory space',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'updates' => [
			'default' => false,
			'description' => 'System update history',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'security' => [
			'default' => false,
			'description' => 'Security',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'stability' => [
			'default' => false,
			'description' => 'System stability configuration',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'libraries' => [
			'default' => false,
			'description' => 'Support for libraries',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'performance' => [
			'default' => false,
			'description' => 'Performance verification',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'publicDirectoryAccess' => [
			'default' => false,
			'description' => 'Public directory',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'environment' => [
			'default' => false,
			'description' => 'Environment information',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'writableFilesAndFolders' => [
			'default' => false,
			'description' => 'Writable files and folders',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
		'database' => [
			'default' => false,
			'description' => 'Database information',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
	],
	'Social' => [
		'TWITTER_ENABLE_FOR_MODULES' => [
			'default' => [],
			'description' => 'List of modules for which Twitter has been enabled.',
		]
	],
	'Branding' => [
		'footerName' => [
			'default' => '',
			'description' => 'Footer\'s name',
			'validation' => function () {
				return true;
			},
			'sanitization' => function () {
				return \App\Purifier::purify(func_get_arg(0));
			}
		],
		'urlLinkedIn' => [
			'default' => 'https://www.linkedin.com/groups/8177576',
			'description' => 'LinkedIn URL',
			'validation' => function () {
				return true;
			},
			'sanitization' => function () {
				return \App\Purifier::purify(func_get_arg(0));
			}
		],
		'urlTwitter' => [
			'default' => 'https://twitter.com/YetiForceEN',
			'description' => 'Twitter URL',
			'validation' => function () {
				return true;
			},
			'sanitization' => function () {
				return \App\Purifier::purify(func_get_arg(0));
			}
		],
		'urlFacebook' => [
			'default' => 'https://www.facebook.com/YetiForce-CRM-158646854306054/',
			'description' => 'Facebook URL',
			'validation' => function () {
				return true;
			},
			'sanitization' => function () {
				return \App\Purifier::purify(func_get_arg(0));
			}
		],
	],
	'MeetingService' => [
		'defaultEmailTemplate' => [
			'default' => [],
			'description' => "List of default email templates.\n@example ['Calendar'=>1]",
		]
	],
	'Phone' => [
		'defaultPhoneCountry' => [
			'default' => true,
			'description' => 'Determines the way the default country in the phone field is downloaded. True retrieves the value from the countries panel, false retrieves the country from the users default language.',
			'validation' => '\App\Validator::bool',
			'sanitization' => '\App\Purifier::bool'
		],
	],
];
