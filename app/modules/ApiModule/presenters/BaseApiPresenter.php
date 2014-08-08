<?php
/**
 * @author Tomáš Blatný
 */

namespace Tempeus\ApiModule;

use Nette\Application\Responses\JsonResponse;
use Nette\Utils\ArrayHash;
use Tempeus\BasePresenter;
use Tempeus\Model\ApiEntityFactory;

abstract class BaseApiPresenter extends BasePresenter
{
	/** @var array */
	protected $data;

	/** @var ApiEntityFactory */
	protected $entityFactory;

	/** @var string @persistent */
	public $version;

	public function startup()
	{
		parent::startup();
		$this->data = array(
			'links' => array(
				'api' => array(
					'default' => $this->link('//Dashboard:'),
				),
				'chat' => array(
					'default' => $this->link('//Chat:'),
					'login' => $this->link('//:Api:Chat:login'),
				),
			),
			'errors' => array(),
		);
		$this->checkVersion();
		$this->entityFactory = new ApiEntityFactory($this);
	}

	public function beforeRender()
	{
		parent::beforeRender();
		if(count($this->data['errors'])) {
			foreach($this->data as $k => $v) {
				if($k !== 'errors') {
					unset($this->data[$k]);
				}
			}
		}
		$this->sendResponse(new JsonResponse($this->data));
	}

	protected function checkVersion()
	{
		if($this->version !== 'v1') {
			$this->addError('Invalid API version number');
		}
	}

	protected function &getData()
	{
		return $this->data;
	}

	protected function addError($message)
	{
		$this->data['errors'][] = $message;
	}
}
