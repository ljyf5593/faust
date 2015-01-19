<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 用户头像组件
 */
class Faust {

	private $uid;

	/**
	 * 存储用户头像尺寸信息
	 */
	private static $avatar_size = array();

	public function __construct($uid){
		$this->uid = $uid;
	}

	/**
	 * 获取某个用户的头像
	 * @static
	 * @param $uid
	 * @param string $size
	 */
	public static function get_avatar($uid, $size = 'big'){
		$file_path = self::get_avatar_path($uid);
		$avatar_file = $file_path.'/'.self::get_avatar_name($uid, $size);
		$avatar_size = self::get_avatar_size();
		if(file_exists(DOCROOT.$avatar_file)){
			return HTML::image($avatar_file, $avatar_size[$size]+array('alt' => '头像'));
		}

		return HTML::image('/media/faust/img/noavatar_'.$size.'.jpg', $avatar_size[$size]+array('alt' => '头像'));
	}

	/**
	 * 保存文件
	 */
	public function save_avatar(){
		$input = file_get_contents('php://input');

		// 这个分隔符是在flash中设定的，不可更改
		$data = explode('--------------------', $input);
		$file_path = $this->get_avatar_path($this->uid);
		file_put_contents(DOCROOT.'/'.$file_path.'/'.$this->get_avatar_name($this->uid, 'big'), $data[0]); // 剪裁后的文件

		if(isset($data[1]) AND $data[1]){  // 如果上传了原始图片
			file_put_contents(DOCROOT.'/'.$file_path.'/'.$this->get_avatar_name($this->uid, 'source'), $data[1]); // 原始文件
		}

		$this->resize_avatar($file_path);

		return 1;
	}

	/**
	 * 裁剪头像
	 * @param $params
	 */
	public function crop_avatar($params){
		// 首先获取用户的原始头像数据
		$avatar_path = $this->get_avatar_path($this->uid);
        $avatar_name = $this->get_avatar_name($this->uid, 'source');
        $source_avatar_path = DOCROOT.$avatar_path.'/'.$avatar_name;
        
        // 如果提交了文件则保存文件数据
        if(isset($params['file'])){
            $this->save_encode_file($params['file'], $source_avatar_path);
        }

        $big_avatar_path = DOCROOT.$avatar_path.'/'.$this->get_avatar_name($this->uid, 'big');
        if(isset($params['avatar'])){
            $this->save_encode_file($params['avatar'], $big_avatar_path);
        } else {
            $source_img = Image::factory($source_avatar_path);
            // 先缩放到展示的尺寸
            $jcropSize = Kohana::$config->load('faust.jcropSize');
            $source_img->resize($jcropSize['width'], $jcropSize['height'], Image::PRECISE);
            
            // 裁剪
            $source_img->crop(
                Arr::get($params, 'w', $jcropSize['width']),
                Arr::get($params, 'h', $jcropSize['height']),
                Arr::get($params, 'x', 0),
                Arr::get($params, 'y', 0)
            );

            // 再缩放到头像的最大尺寸
            $avatar_size = $this->get_avatar_size();
            $source_img->resize($avatar_size['big']['width'], $avatar_size['big']['width'], Image::WIDTH);
            $source_img->save($big_avatar_path);
        }

       	$this->resize_avatar($avatar_path);

       	return true;
	}

	/**
	 * 保存用户上传头像的原始图片
	 */
	public function set_avatar_source($file){
        $result = array(
            'status' => 'error',
            'message' => '操作失败',
        );
        if (Upload::not_empty($file)){
            if (Upload::valid($file)){ //检查数据是否正常
                // 检测文件类型
                if (Upload::type($file, array('jpg', 'png'))){
                    $avatar_path = $this->get_avatar_path($this->uid);
                    $avatar_name = $this->get_avatar_name($this->uid, 'source');
                    
                    if(Upload::save($file, $avatar_name, DOCROOT.$avatar_path, FALSE)){
                        $result['status'] = 'success';
                        $result['message'] = '操作成功';
                        $result['file'] = $avatar_path.'/'.$avatar_name;
                    }
                }else {
                    $result['message'] = '非法文件类型';
                }

            }else {
                $result['message'] = '文件数据错误，请重新选择上传';
            }
        }else {
            $result['message'] = '请选择文件';
        }

        return $result;
	}

	/**
	 * 获取原始头像图片
	 * @return array|bool|string
	 */
	public function get_avatar_source(){
		$source = $this->check_avatar('source');
		return $source ? $source : 'media/faust/img/noavatar.jpg';
	}

	/**
	 * 判断用户是否已经上传过头像
     * 如果是则返回头像地址，否则返回空
	 */
	public function check_avatar($size='big'){
		$file_path = self::get_avatar_path($this->uid);
		$avatar_file = $file_path.'/'.$this->get_avatar_name($this->uid, $size);
		if(file_exists(DOCROOT.$avatar_file)){
			return $avatar_file;
		}

		return FALSE;
	}

	/**
	 * 获取图片的尺寸，读取配置文件
	 * @static
	 */
	public static function get_avatar_size(){
		// 默认尺寸
		$default_size = array(
			'big' => array(
				'width' => 110,
				'height' => 135,
			),
			'middle' => array(
				'width' => 74,
				'height' => 90,
			),
			'small' => array(
				'width' => 65,
				'height' => 80,
			),
		);

		if(empty(self::$avatar_size)){
			$pSize = Kohana::$config->load('faust.pSize');
			$pSize = explode('|', $pSize);
			$i = 2;
			foreach($default_size as $key => $value){
				self::$avatar_size[$key]['width'] = Arr::get($pSize, $i++, $value['width']);
				self::$avatar_size[$key]['height'] = Arr::get($pSize, $i++, $value['height']);
			}
		}

		return self::$avatar_size;
	}

	/**
	 * 剪裁头像，这里只需要剪裁中头像和小头像
	 */
	private function resize_avatar($file_path){
		$avatar_size = self::get_avatar_size();
		$avatar_file = DOCROOT.$file_path.'/';
		$big_avatar = Image::factory($avatar_file.$this->get_avatar_name($this->uid, 'big'));

		// 不裁剪大头像，因为大头像已经生成了
		unset($avatar_size['big']);
		foreach($avatar_size as $key => $value){
			$resize_avatar = $avatar_file.$this->get_avatar_name($this->uid, $key);
			$big_avatar->resize($value['width'], $value['height'])->save($resize_avatar);
		}
	}

	/**
	 * 获取文件路径
	 */
	private static function get_avatar_path($uid){
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$realpath = '/avatar/'.$dir1.'/'.$dir2.'/'.$dir3;
		if(!is_dir( DOCROOT.$realpath )) {
			mkdir(DOCROOT.$realpath, 02777, TRUE);
		}
		return $realpath;
	}

    /**
     * 获取头像文件名称
     */
	private static function get_avatar_name($uid, $size){
		$uid = sprintf("%09d", $uid);
		return 	substr($uid, 7, 2).'_avatar_'.$size.'.jpg';
	}

    /**
     * 保存通过http传递过来的文件
     * @param $file_data 编码后的文件
     * @param $path 要保存的文件路面
     */
    private function save_encode_file($file_data, $path){
        $data_start = strpos($file_data, ',');
        $data = base64_decode(substr($file_data, $data_start+1));
        file_put_contents($path, $data);
    }

	/**
	 * 获取二进制文件的类型
	 * @param $file 文件流
	 */
	private function get_file_type($file){
		$bin = substr($file, 0, 2); //只读2字节
		$str_info  = @unpack("C2chars", $bin);
		$type_code = intval($str_info['chars1'].$str_info['chars2']);
		$file_type = '';
		switch ($type_code) {
			case 7790:
				$file_type = 'exe';
				break;
			case 7784:
				$file_type = 'midi';
				break;
			case 8075:
				$file_type = 'zip';
				break;
			case 8297:
				$file_type = 'rar';
				break;
			case 255216:
				$file_type = 'jpg';
				break;
			case 7173:
				$file_type = 'gif';
				break;
			case 6677:
				$file_type = 'bmp';
				break;
			case 13780:
				$file_type = 'png';
				break;
			default:
				$file_type = 'unknown';
				break;
		}

		return $file_type;
	}
}
