<?php
/**
 * ownCloud - transmissionwebclient
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Galambos Máté <matega@mensa.hu>
 * @copyright Galambos Máté 2015
 */

namespace OCA\Transmission\Controller;

use OCP\IRequest;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ContentSecurityPolicy;

class PageController extends Controller {


	private $userId;

	public function __construct($AppName, IRequest $request, $UserId){
		parent::__construct($AppName, $request);
		$this->userId = $UserId;
	}

	/**
	 * CAUTION: the @Stuff turns off security checks; for this page no admin is
	 *          required and no CSRF check. If you don't know what CSRF is, read
	 *          it up in the docs or you might create a security hole. This is
	 *          basically the only required method to add this exemption, don't
	 *          add it to any other method if you don't exactly know what it does
	 *
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function index() {
		$rsp = new TemplateResponse('transmission', 'main');
		$csp = new ContentSecurityPolicy();
		$csp->addAllowedFrameDomain("'self'");
		$rsp->setContentSecurityPolicy($csp);
		return $rsp;
	}
}
