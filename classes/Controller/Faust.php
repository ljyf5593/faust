<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Faust extends Controller {

	/**
	 * 显示flash
	 */
	public function action_flash(){
		$user = Auth::instance()->get_user();
		$faust = new Faust($user->pk());
		$config = Kohana::$config->load('faust');
		$config['imgUrl'] = URL::site($faust->get_avatar_source());
		$config['uploadUrl'] = URL::site('/faust/upload');
		$config['uploadSrc'] = true; // 上传原始文件
		$view = View::factory('faust/flash')->set('config', $config);
		$this->response->body($view);
	}

	/**
	 * Flash文件上传
	 */
	public function action_upload(){
		$result = array();
		$user = Auth::instance()->get_user();
		if($user){

			$faust = new Faust($user->pk());
			$result['status'] = $faust->save_avatar();

		} else {
			$result['status'] = -3; // 需要登录
		}

		$this->response->body(json_encode($result));
	}

	/**
	 * html头像上传
	 */
	public function action_html(){
		$this->response->body(View::factory('faust/html'));
	}

	/**
	 * jcrop视图
	 */
	public function action_jcrop(){
		$user = Auth::instance()->get_user();
		$faust = new Faust($user->pk());
		$config = Kohana::$config->load('faust');
		$config['imgUrl'] = URL::site($faust->get_avatar_source());
		$config['uploadUrl'] = URL::site('/faust/upload');
		$view = View::factory('faust/html/jcrop')->set(array(
			'jcrop_size' => Kohana::$config->load('faust.jcropSize'),
			'avatar_source' => URL::site($faust->get_avatar_source()),
			'avatar_size' => Faust::get_avatar_size(),
		));
		$this->response->body($view);
	}

	/**
	 * jcrop和FileReader方式裁剪
	 */
	public function action_crop(){
		$json = array(
			'status' => 'error',
			'message' => '操作失败',
		);
		$user = Auth::instance()->get_user();
		$faust = new Faust($user->pk());
		if($faust->crop_avatar($this->request->post())){
			$json = array(
				'status' => 'success',
				'message' => '操作成功',
			);
		}

		$this->response->body(json_encode($json));
	}

	/**
	 * canvascropper视图方式
	 */
	public function action_canvas(){
		$user = Auth::instance()->get_user();
		$faust = new Faust($user->pk());
		$config = Kohana::$config->load('faust');
		$config['imgUrl'] = URL::site($faust->get_avatar_source());
		$config['uploadUrl'] = URL::site('/faust/upload');
		$view = View::factory('faust/html/canvas')->set(array(
			'jcrop_size' => Kohana::$config->load('faust.jcropSize'),
			'avatar_source' => URL::site($faust->get_avatar_source()),
			'avatar_size' => Faust::get_avatar_size(),
		));
		$this->response->body($view);
	}

	/**
	 * canvas 方式，待完成 todo
	 */
	public function action_fileReader(){
		$file_data = $this->request->post('file');
		$data_start = strpos($file_data, ',');
		$data = base64_decode(substr($file_data, $data_start+1));
		file_put_contents("simpletext.jpg", $data);
	}

	/**
	 * Ajax文件上传
	 */
	public function action_ajax(){
		$user = Auth::instance()->get_user();
		$faust = new Faust($user->pk());
		echo json_encode($faust->set_avatar_source($_FILES['file']));
		exit();
	}
}