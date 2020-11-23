<?php
/**
 * Users logout action class.
 *
 * @license YetiForce Public License 3.0 (licenses/LicenseEN.txt or yetiforce.com)
 * @author Mariusz Krzaczkowski <m.krzaczkowski@yetiforce.com>
 */

namespace Api\Portal\Users;

use OpenApi\Annotations as OA;

/**
 * Logout class.
 */
class Logout extends \Api\Core\BaseAction
{
	/** @var string[] Allowed request methods */
	public $allowedMethod = ['PUT'];

	/**
	 * Check permission to module.
	 *
	 * @throws \Api\Core\Exception
	 *
	 * @return bool
	 */
	public function checkPermissionToModule()
	{
		return true;
	}

	/**
	 * Put method.
	 *
	 * @return bool
	 *
	 * @OA\Put(
	 *		path="/webservice/Users/Logout",
	 *		summary="Logout user out the system",
	 *		tags={"Users"},
	 *		security={
	 *			{"basicAuth" : "", "ApiKeyAuth" : "", "token" : ""}
	 *    },
	 *		@OA\RequestBody(
	 *  			required=false,
	 * 				description="Users logout request body",
	 *	  ),
	 *    @OA\Parameter(
	 *        name="X-ENCRYPTED",
	 *        in="header",
	 *        required=true,
	 * 			@OA\Schema(ref="#/components/schemas/X-ENCRYPTED")
	 *    ),
	 *		@OA\Response(
	 *			response=200,
	 *			description="User details",
	 *			@OA\JsonContent(ref="#/components/schemas/UsersLogoutResponseBody"),
	 *			@OA\XmlContent(ref="#/components/schemas/UsersLogoutResponseBody"),
	 *		),
	 * ),
	 * @OA\SecurityScheme(
	 *		securityScheme="token",
	 *   	type="apiKey",
	 *    in="header",
	 * 		name="X-TOKEN",
	 *   	description="Webservice api token, generated when logging into the system, required for communication"
	 * ),
	 * @OA\Schema(
	 * 		schema="UsersLogoutResponseBody",
	 * 		title="Users module - Users logout response body",
	 * 		description="JSON data",
	 *		type="object",
	 * 		@OA\Property(
	 *       	property="status",
	 *        description="A numeric value of 0 or 1 that indicates whether the communication is valid. 1 - success , 0 - error",
	 * 				enum={0, 1},
	 *     	  type="integer",
	 *        example=1
	 * 		),
	 *    @OA\Property(
	 *     	  property="result",
	 *     	 	description="Content of responses from a given method",
	 *    	 	type="boolean",
	 *    ),
	 * ),
	 */
	public function put()
	{
		$db = \App\Db::getInstance('webservice');
		$db->createCommand()->delete('w_#__portal_session', [
			'id' => $this->controller->headers['x-token'],
		])->execute();
		$db->createCommand()
			->update('w_#__portal_user', [
				'logout_time' => date('Y-m-d H:i:s'),
			], ['id' => $this->session->get('id')])
			->execute();

		return true;
	}
}
