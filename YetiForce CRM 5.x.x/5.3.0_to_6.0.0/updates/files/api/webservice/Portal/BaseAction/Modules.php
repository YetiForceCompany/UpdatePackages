<?php

/**
 * Get modules list action class.
 *
 * @copyright YetiForce Sp. z o.o
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\BaseAction;

use OpenApi\Annotations as OA;

class Modules extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['GET'];

	/**
	 * Get method.
	 *
	 * @return array
	 *
	 * @OA\Get(
	 *		path="/webservice/Modules",
	 *		summary="Get the module list action, along with their translated action",
	 *		tags={"BaseAction"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *		},
	 *		@OA\RequestBody(
	 *				required=false,
	 *				description="The content of the request is empty",
	 *		),
	 *		@OA\Parameter(
	 *			name="X-ENCRYPTED",
	 *			in="header",
	 *			required=true,
	 *				@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *		),
	 *		@OA\Response(
	 *			response=200,
	 *			description="List of active modules",
	 *			@OA\JsonContent(ref="#/components/schemas/BaseActionModulesResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/BaseActionModulesResponseBody"),
	 *		),
	 *		@OA\Response(
	 *				response=401,
	 *				description="No sent token OR Invalid token",
	 *		),
	 *		@OA\Response(
	 *				response=403,
	 *				description="No permissions for module",
	 *		),
	 * ),
	 * @OA\Schema(
	 *		schema="BaseActionModulesResponseBody",
	 *		title="Base action - List of modules",
	 *		description="List of available modules",
	 *		type="object",
	 *		@OA\Property(
	 *			property="status",
	 *			description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 *			enum={"0", "1"},
	 *			type="integer",
	 *		),
	 *		@OA\Property(
	 *			property="result",
	 *			description="List of modules accessed",
	 *			type="object",
	 *			@OA\AdditionalProperties(description="Module name", type="string", example="Accounts"),
	 * 		),
	 *	),
	 */
	public function get()
	{
		return \Api\Core\Module::getPermittedModules();
	}
}
